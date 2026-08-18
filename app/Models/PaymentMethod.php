<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * One way of paying, as configured for this installation.
 *
 * The row says whether the method is offered and holds its credentials. What
 * the method *is* — which fields it needs, how it takes money — lives in
 * `PaymentMethods` in code, because a payment method is behaviour, and
 * behaviour cannot be created by inserting a row.
 */
class PaymentMethod extends Model
{
    use HasTranslations;

    public const CODE_STRIPE = 'stripe';
    public const CODE_BANK_TRANSFER = 'bank_transfer';

    public array $translatable = ['instructions'];

    protected $fillable = [
        'code',
        'is_enabled',
        'sort_order',
        'settings',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** One setting, with a default when it was never filled in. */
    public function setting(string $key, mixed $default = null): mixed
    {
        $value = $this->settings[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    /**
     * Whether this method has everything it needs to actually take money.
     *
     * Being switched on is not enough: a bank transfer without an account
     * number is a page telling the customer to send money nowhere.
     */
    public function isUsable(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        return match ($this->code) {
            self::CODE_STRIPE => filled($this->setting('secret_key')) || filled(config('services.stripe.secret')),
            self::CODE_BANK_TRANSFER => filled($this->setting('account_number')) || filled($this->setting('iban')),
            default => false,
        };
    }

    /** Proč metoda nejde použít — pro administraci, ne pro kupujícího. */
    public function unusableReason(): ?string
    {
        if ($this->isUsable()) {
            return null;
        }

        if (! $this->is_enabled) {
            return 'Metoda je vypnutá.';
        }

        return match ($this->code) {
            self::CODE_STRIPE => 'Chybí tajný klíč Stripe.',
            self::CODE_BANK_TRANSFER => 'Chybí číslo účtu i IBAN — kupující by neměl kam poslat peníze.',
            default => 'Neznámá metoda.',
        };
    }
}
