<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\MemberSubscription;
use App\Models\SubscriptionType;
use App\Services\Payments\StripeGateway;
use App\Support\Currencies;
use App\Support\OfflineCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

/**
 * Stripe checkout for the member Premium membership.
 *
 * Kept separate from SubscriptionCheckoutController, which sells VIP tiers to
 * providers and lives behind `gender:female` + `profile.exists` middleware.
 * A member has no profile, so it could not have been reused.
 *
 * Deliberately renders no views: `success` redirects back to the member
 * dashboard with a flash message. The activation itself happens in the
 * webhook, which is the only source of truth for "was this actually paid".
 */
class MembershipCheckoutController extends \Illuminate\Routing\Controller
{
    /**
     * Plans available to members, plus the membership currently in force.
     * Returned as data so any caller (page, panel, API) can render it.
     *
     * @return array{types: \Illuminate\Support\Collection, active: ?MemberSubscription}
     */
    public static function membershipOptions(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        return [
            'types' => SubscriptionType::active()->forMembers()->ordered()->get(),
            'active' => $userId
                ? MemberSubscription::forUser($userId)->active()->latest('ends_at')->first()
                : null,
        ];
    }

    /**
     * The plan list a member buys from — the destination of the sidebar's
     * "Začít PRÉMIUM" button and of the profile detail's "Obnovit přístup".
     */
    public function index()
    {
        return view('member.membership', self::membershipOptions());
    }

    public function checkout(Request $request, SubscriptionType $subscriptionType)
    {
        $user = Auth::user();

        abort_unless($subscriptionType->isForMembers(), 404);
        abort_unless($subscriptionType->is_active, 404);

        // No gateway: finish the order without taking money, so the flow can be
        // walked end to end. The subscription is marked as manual, and the
        // buyer is told plainly that nothing was charged.
        if (OfflineCheckout::shouldHandle()) {
            return $this->completeWithoutPayment($user, $subscriptionType);
        }

        // Missing keys are a deployment problem, not something the buyer did.
        // Without this the Stripe client threw "$config must be a string or an
        // array" and the customer got a 500 on the checkout route.
        $stripe = StripeGateway::client();

        if (! $stripe) {
            Log::error('Membership checkout attempted with Stripe not configured', [
                'user_id' => $user->id,
                'subscription_type_id' => $subscriptionType->id,
            ]);

            return back()->with('error', __('front.membership.payments_unavailable'));
        }

        // Charge in the currency the plan is priced in and the buyer was shown,
        // rather than assuming korunas.
        $currency = Currencies::forLocale();
        $amount = $subscriptionType->priceIn($currency);

        if ($amount === null) {
            $currency = Currency::base()?->code ?? Currencies::CZK;
            $amount = $subscriptionType->priceIn($currency);
        }

        if ($amount === null || $amount <= 0) {
            Log::error('Membership plan has no usable price', [
                'subscription_type_id' => $subscriptionType->id,
                'currency' => $currency,
            ]);

            return back()->with('error', __('front.membership.price_unavailable'));
        }

        try {
            $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $user->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => StripeGateway::amountInMinorUnits($amount, $currency),
                    'product_data' => [
                        'name' => $subscriptionType->getTranslation('name', app()->getLocale()),
                    ],
                ],
            ]],
            'success_url' => route('account.member.membership.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('account.member.membership.cancel'),
            // `member_user_id` is what tells the shared webhook this is a
            // membership rather than a profile VIP tier.
            'metadata' => [
                'member_user_id' => $user->id,
                'subscription_type_id' => $subscriptionType->id,
            ],
            ]);
        } catch (\Throwable $e) {
            // A refused or misconfigured gateway is reported, not rendered as
            // a stack trace.
            Log::error('Stripe membership checkout failed', [
                'user_id' => $user->id,
                'subscription_type_id' => $subscriptionType->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('front.membership.checkout_failed'));
        }

        return redirect($session->url);
    }

    /**
     * Grant the membership without a payment, then report it as such.
     *
     * Renewing extends the running membership instead of stacking a second
     * row, exactly as the paid path does.
     */
    private function completeWithoutPayment($user, SubscriptionType $subscriptionType)
    {
        $existing = MemberSubscription::forUser($user->id)->active()->latest('ends_at')->first();

        if ($existing) {
            $existing->renew($subscriptionType->duration_days);
            $existing->update(['metadata' => OfflineCheckout::metadata()]);
        } else {
            MemberSubscription::create([
                'user_id' => $user->id,
                'subscription_type_id' => $subscriptionType->id,
                'status' => MemberSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'ends_at' => now()->addDays($subscriptionType->duration_days),
                'metadata' => OfflineCheckout::metadata(),
            ]);
        }

        Log::info('Membership granted without payment (no gateway configured)', [
            'user_id' => $user->id,
            'subscription_type_id' => $subscriptionType->id,
        ]);

        return redirect()
            ->route('account.member.dashboard')
            ->with('status', __('front.membership.activated_without_payment'));
    }

    /**
     * Stripe returns here after checkout. The membership is created by the
     * webhook, so this only reports what actually happened — it never claims
     * success on its own.
     */
    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        $user = Auth::user();

        $paid = false;

        if ($sessionId !== '') {
            try {
                $session = StripeGateway::client()
                    ?->checkout->sessions->retrieve($sessionId);

                $paid = ($session->payment_status ?? null) === 'paid'
                    && (int) ($session->metadata->member_user_id ?? 0) === $user->id;
            } catch (\Throwable $e) {
                Log::warning('Could not verify Stripe membership session', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $activated = $paid && MemberSubscription::query()
            ->where('user_id', $user->id)
            ->where('metadata->stripe_session_id', $sessionId)
            ->exists();

        $status = match (true) {
            $activated => __('front.membership.activated'),
            $paid => __('front.membership.activation_pending'),
            default => __('front.membership.not_verified'),
        };

        return redirect()
            ->route('account.member.dashboard')
            ->with($paid ? 'status' : 'error', $status);
    }

    public function cancel()
    {
        return redirect()
            ->route('account.member.dashboard')
            ->with('status', __('front.membership.checkout_cancelled'));
    }

    /**
     * Turn a paid Stripe session into a membership.
     *
     * Called from the shared webhook in SubscriptionCheckoutController. Renewing
     * an existing membership extends it instead of stacking a second row, so a
     * member who buys twice keeps one continuous end date.
     */
    public static function activateFromSession(object $session): void
    {
        $userId = $session->metadata->member_user_id ?? null;
        $typeId = $session->metadata->subscription_type_id ?? null;

        if (! $userId || ! $typeId) {
            Log::warning('Stripe membership session missing metadata', ['session_id' => $session->id]);

            return;
        }

        // Stripe retries webhooks; the session id keeps this idempotent.
        $alreadyProcessed = MemberSubscription::query()
            ->where('metadata->stripe_session_id', $session->id)
            ->exists();

        if ($alreadyProcessed) {
            return;
        }

        $type = SubscriptionType::find($typeId);

        if (! $type || ! $type->isForMembers()) {
            Log::warning('Stripe membership session referenced a non-member plan', [
                'session_id' => $session->id,
                'subscription_type_id' => $typeId,
            ]);

            return;
        }

        $metadata = [
            'stripe_session_id' => $session->id,
            'stripe_payment_intent' => $session->payment_intent ?? null,
            'amount_total' => $session->amount_total ?? null,
            'currency' => $session->currency ?? null,
        ];

        $existing = MemberSubscription::forUser($userId)->active()->latest('ends_at')->first();

        if ($existing) {
            $existing->renew($type->duration_days);
            $existing->update(['metadata' => $metadata]);

            return;
        }

        MemberSubscription::create([
            'user_id' => $userId,
            'subscription_type_id' => $type->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($type->duration_days),
            'status' => MemberSubscription::STATUS_ACTIVE,
            'auto_renew' => false,
            'metadata' => $metadata,
        ]);
    }
}
