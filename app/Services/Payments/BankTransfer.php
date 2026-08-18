<?php

namespace App\Services\Payments;

use App\Models\MemberSubscription;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

/**
 * Paying by moving money between accounts, which takes days.
 *
 * With a card the order and the payment are one event: the gateway answers
 * before the buyer leaves the page. A transfer separates them, and the gap is
 * the whole point of this class — the order exists, the customer has done
 * their part, and nobody can say yet whether the amount landed.
 *
 * So nothing is activated here. The subscription is created `pending`, the
 * customer is given an account number and a reference, and an administrator
 * confirms it once they see the money. Activating on click would mean the site
 * hands out a paid product on the strength of somebody clicking a button.
 */
class BankTransfer
{
    /**
     * The reference the customer puts on the payment.
     *
     * Without it a transfer arriving at the bank is an anonymous amount and
     * matching it to an order is guesswork. Derived from the subscription's own
     * id so it is unique, short enough for the bank's field, and — the useful
     * part — readable backwards: the operator can find the order from the
     * statement without opening anything.
     */
    public function reference(Model $subscription): string
    {
        $prefix = $subscription instanceof MemberSubscription ? '2' : '1';

        return $prefix . str_pad((string) $subscription->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Everything the customer needs in order to pay.
     *
     * @return array<string, string|null>
     */
    public function instructions(Model $subscription, float $amount, string $currency): array
    {
        $method = PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER);

        return [
            'account_number' => $method?->setting('account_number'),
            'iban' => $method?->setting('iban'),
            'swift' => $method?->setting('swift'),
            'holder' => $method?->setting('account_holder'),
            'bank' => $method?->setting('bank_name'),
            'reference' => $subscription->payment_reference ?: $this->reference($subscription),
            'amount' => number_format($amount, 2, ',', ' ') . ' ' . strtoupper($currency),
            'note' => $method?->getTranslation('instructions', app()->getLocale(), false) ?: null,
        ];
    }

    /**
     * Confirm the money arrived, and start the subscription from that moment.
     *
     * Deliberately dated from the confirmation, not from the order: a customer
     * who paid three days late should get their full month, not a month that
     * started while they were still deciding.
     */
    public function confirm(Model $subscription, ?int $confirmedBy = null): Model
    {
        $days = (int) ($subscription->type->duration_days ?? 30);

        $subscription->forceFill([
            'status' => $subscription instanceof Subscription
                ? Subscription::STATUS_ACTIVE
                : MemberSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays(max(1, $days)),
            'paid_at' => now(),
            'payment_confirmed_by' => $confirmedBy,
        ])->save();

        return $subscription->refresh();
    }

    /** Whether this order is still waiting for the money. */
    public function isAwaitingPayment(Model $subscription): bool
    {
        return $subscription->payment_method === PaymentMethod::CODE_BANK_TRANSFER
            && $subscription->paid_at === null
            && $subscription->status === Subscription::STATUS_PENDING;
    }
}
