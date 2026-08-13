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

        $types = SubscriptionType::active()->orderBy('sort_order')->get();

        $activeSubscription = $profile
            ? $profile->subscriptions()
                ->where('status', Subscription::STATUS_ACTIVE)
                ->where('ends_at', '>', now())
                ->latest('ends_at')
                ->first()
            : null;

        return view('account.subscription', compact('types', 'activeSubscription'));
    }

    public function checkout(Request $request, SubscriptionType $subscriptionType)
    {
        $user = Auth::user();
        $profile = $user->profile;

        abort_unless($profile, 403, __('front.subscription.no_profile'));

        $stripe = new StripeClient(config('services.stripe.secret'));

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

    public function success(Request $request)
    {
        return view('account.subscription-success', [
            'sessionId' => $request->query('session_id'),
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
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->activateSubscription($session);
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
}
