<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionCheckoutController extends \Illuminate\Routing\Controller
{
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;

        // Scoped to provider plans: since member Premium plans live in the same
        // table (distinguished by `audience`), an unscoped query would offer a
        // provider the membership meant for members.
        $types = SubscriptionType::active()->forProfiles()->orderBy('sort_order')->get();

        $activeSubscription = $profile
            ? $profile->subscriptions()
                ->where('status', Subscription::STATUS_ACTIVE)
                ->where('ends_at', '>', now())
                ->latest('ends_at')
                ->first()
            : null;

        $paymentMethods = \App\Services\Payments\PaymentMethods::available();

        // Objednávka, která čeká na peníze. Do doby, než dorazí, je jediné,
        // co kupující chce vidět, číslo účtu a variabilní symbol — ne další
        // nabídka tarifů.
        $awaitingPayment = $profile
            ? $profile->subscriptions()
                ->where('status', Subscription::STATUS_PENDING)
                ->where('payment_method', \App\Models\PaymentMethod::CODE_BANK_TRANSFER)
                ->whereNull('paid_at')
                ->latest()
                ->first()
            : null;

        $transferInstructions = $awaitingPayment
            ? app(\App\Services\Payments\BankTransfer::class)->instructions(
                $awaitingPayment,
                (float) ($awaitingPayment->type->price ?? 0),
                'CZK',
            )
            : null;

        return view('account.subscription', compact(
            'types',
            'activeSubscription',
            'paymentMethods',
            'awaitingPayment',
            'transferInstructions',
        ));
    }

    public function checkout(Request $request, SubscriptionType $subscriptionType)
    {
        $user = Auth::user();
        $profile = $user->profile;

        abort_unless($profile, 403, __('front.subscription.no_profile'));

        // Bankovní převod: objednávka vznikne, peníze dorazí za pár dní.
        // Aktivace až po potvrzení v administraci — jinak by web vydával
        // placený produkt na to, že někdo klikl na tlačítko.
        $chosen = (string) $request->input('payment_method', '');

        if ($chosen === \App\Models\PaymentMethod::CODE_BANK_TRANSFER
            && \App\Services\Payments\PaymentMethods::isAvailable($chosen)) {
            return $this->awaitBankTransfer($profile, $subscriptionType);
        }

        // No gateway: finish the order without taking money, so the flow can be
        // walked end to end. Same rule as the membership checkout.
        if (\App\Support\OfflineCheckout::shouldHandle()) {
            return $this->completeWithoutPayment($profile, $subscriptionType);
        }

        // Same guard as the membership checkout: missing keys are a deployment
        // problem and must not reach the buyer as a 500.
        $stripe = \App\Services\Payments\StripeGateway::client();

        if (! $stripe) {
            \Illuminate\Support\Facades\Log::error('Subscription checkout attempted with Stripe not configured', [
                'user_id' => $user->id,
                'subscription_type_id' => $subscriptionType->id,
            ]);

            return back()->with('error', __('front.membership.payments_unavailable'));
        }

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $user->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'czk',
                    'unit_amount' => (int) round($subscriptionType->price * 100),
                    'product_data' => [
                        'name' => $subscriptionType->getTranslation('name', app()->getLocale()),
                    ],
                ],
            ]],
            'success_url' => route('account.subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('account.subscription.cancel'),
            'metadata' => [
                'profile_id' => $profile->id,
                'subscription_type_id' => $subscriptionType->id,
            ],
        ]);

        return redirect($session->url);
    }

    /**
     * Create the order and hand over the payment details.
     *
     * Nothing is activated: the subscription is `pending` until somebody
     * confirms the money arrived. The variable symbol is what turns an
     * anonymous amount on a bank statement back into this order.
     */
    private function awaitBankTransfer($profile, SubscriptionType $subscriptionType)
    {
        $transfer = app(\App\Services\Payments\BankTransfer::class);

        $subscription = Subscription::create([
            'profile_id' => $profile->id,
            'subscription_type_id' => $subscriptionType->id,
            'status' => Subscription::STATUS_PENDING,
            'payment_method' => \App\Models\PaymentMethod::CODE_BANK_TRANSFER,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $subscription->forceFill([
            'payment_reference' => $transfer->reference($subscription),
        ])->save();

        return redirect()
            ->route('account.subscription.index')
            // Klíč `status`, protože ten stránka vypisuje.
            ->with('status', __('front.payments.transfer_created'));
    }

    /**
     * Stripe redirects here after checkout.
     *
     * This used to render an unconditional "payment successful" page for
     * anything that hit the URL, including a hand-typed one — the page said the
     * subscription was active while the webhook (which is what actually creates
     * it) may not have arrived, or may never arrive.
     *
     * The session is now verified against Stripe, and the page distinguishes
     * "paid and active", "paid, activation pending" and "not paid".
     */
    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        $profile = Auth::user()?->profile;

        $paid = false;
        $subscription = null;

        if ($sessionId !== '') {
            try {
                $session = \App\Services\Payments\StripeGateway::client()
                    ?->checkout->sessions->retrieve($sessionId);

                $paid = ($session->payment_status ?? null) === 'paid';

                // Only trust a session that belongs to this user's profile.
                if ($paid && $profile && (int) ($session->metadata->profile_id ?? 0) !== $profile->id) {
                    $paid = false;
                }
            } catch (\Throwable $e) {
                Log::warning('Could not verify Stripe checkout session', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($paid && $profile) {
            $subscription = Subscription::query()
                ->where('profile_id', $profile->id)
                ->where('metadata->stripe_session_id', $sessionId)
                ->first();
        }

        // Aktivace bez platby se sem dostane přímo, bez relace Stripe.
        if ($sessionId === '' && $request->boolean('granted')) {
            $paid = true;
            $subscription = $profile?->subscriptions()
                ->where('status', Subscription::STATUS_ACTIVE)
                ->latest('ends_at')
                ->first();
        }

        return view('account.subscription-success', [
            'sessionId' => $sessionId,
            'paid' => $paid,
            // Null while the webhook has not been processed yet.
            'subscription' => $subscription,
        ]);
    }

    public function cancel()
    {
        return redirect()
            ->route('account.subscription.index')
            ->with('status', __('front.subscription.checkout_cancelled'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = \App\Services\Payments\StripeGateway::webhookSecret();

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // One Stripe endpoint serves both products. The metadata says which:
            // `member_user_id` is a member's Premium membership, `profile_id` is
            // a provider's VIP tier.
            if (! empty($session->metadata->member_user_id)) {
                MembershipCheckoutController::activateFromSession($session);
            } else {
                $this->activateSubscription($session);
            }
        }

        return response('OK', Response::HTTP_OK);
    }

    private function activateSubscription(object $session): void
    {
        $profileId = $session->metadata->profile_id ?? null;
        $subscriptionTypeId = $session->metadata->subscription_type_id ?? null;

        if (! $profileId || ! $subscriptionTypeId) {
            Log::warning('Stripe checkout session missing metadata', ['session_id' => $session->id]);

            return;
        }

        $alreadyProcessed = Subscription::query()
            ->where('metadata->stripe_session_id', $session->id)
            ->exists();

        if ($alreadyProcessed) {
            return;
        }

        $subscriptionType = SubscriptionType::find($subscriptionTypeId);

        if (! $subscriptionType) {
            return;
        }

        Subscription::create([
            'profile_id' => $profileId,
            'subscription_type_id' => $subscriptionType->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($subscriptionType->duration_days),
            'status' => Subscription::STATUS_ACTIVE,
            'auto_renew' => false,
            'metadata' => [
                'stripe_session_id' => $session->id,
                'stripe_payment_intent' => $session->payment_intent ?? null,
                'amount_total' => $session->amount_total ?? null,
                'currency' => $session->currency ?? null,
            ],
        ]);
    }

    /**
     * Grant the subscription without a payment, then report it as such.
     *
     * Only reached when no gateway is configured — see App\Support\
     * OfflineCheckout for why that is allowed and how it stays visible.
     */
    private function completeWithoutPayment($profile, SubscriptionType $subscriptionType)
    {
        Subscription::create([
            'profile_id' => $profile->id,
            'subscription_type_id' => $subscriptionType->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($subscriptionType->duration_days),
            'status' => Subscription::STATUS_ACTIVE,
            'auto_renew' => false,
            'metadata' => \App\Support\OfflineCheckout::metadata(),
        ]);

        \Illuminate\Support\Facades\Log::info('Subscription granted without payment (no gateway configured)', [
            'profile_id' => $profile->id,
            'subscription_type_id' => $subscriptionType->id,
        ]);

        // Na tutéž informativní stránku jako návrat z brány. Dvě cesty ke
        // stejnému výsledku končily různě: jedna stránkou, druhá hláškou,
        // která zmizí při prvním kliknutí.
        return redirect()->route('account.subscription.success', ['granted' => 1]);
    }
}
