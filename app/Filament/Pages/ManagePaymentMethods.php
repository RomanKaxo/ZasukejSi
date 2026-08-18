<?php

namespace App\Filament\Pages;

use App\Models\PaymentMethod;
use App\Services\Payments\PaymentMethods;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * How customers may pay, and with whose credentials.
 *
 * All of this lived in the environment file, so switching a payment on,
 * correcting a key or changing the bank account meant a deploy and somebody
 * with shell access. The person who notices that the account number changed is
 * almost never that person.
 *
 * A method that is switched on but not filled in is worse than one that is
 * off — it offers the customer a way to pay that leads nowhere — so the page
 * says plainly which methods are actually usable.
 */
class ManagePaymentMethods extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 24;

    protected string $view = 'filament.pages.manage-payment-methods';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Nastavení';
    }

    public static function getNavigationLabel(): string
    {
        return 'Platební metody';
    }

    public function getTitle(): string
    {
        return 'Platební metody';
    }

    /** Chybí tabulka — kód je nasazený, migrace ještě neproběhly. */
    public bool $pendingMigration = false;

    public function mount(): void
    {
        PaymentMethods::sync();

        // Mezi nasazením kódu a spuštěním migrací tabulka neexistuje. Je to
        // normálních pár minut života každého vydání a stránka na to nesmí
        // umřít — má říct, co udělat.
        $this->pendingMigration = ! PaymentMethods::ready();

        if ($this->pendingMigration) {
            Notification::make()
                ->title('Chybí databázová tabulka platebních metod')
                ->body('Na serveru ještě neproběhly migrace. Spusťte `php artisan migrate` a stránku načtěte znovu.')
                ->warning()
                ->persistent()
                ->send();

            $this->form->fill();

            return;
        }

        $stripe = PaymentMethods::find(PaymentMethod::CODE_STRIPE);
        $bank = PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER);

        $this->form->fill([
            'stripe_enabled' => (bool) $stripe?->is_enabled,
            'stripe_public_key' => $stripe?->setting('public_key'),
            'stripe_secret_key' => $stripe?->setting('secret_key'),
            'stripe_webhook_secret' => $stripe?->setting('webhook_secret'),

            'bank_enabled' => (bool) $bank?->is_enabled,
            'bank_account_holder' => $bank?->setting('account_holder'),
            'bank_account_number' => $bank?->setting('account_number'),
            'bank_name' => $bank?->setting('bank_name'),
            'bank_iban' => $bank?->setting('iban'),
            'bank_swift' => $bank?->setting('swift'),
            'bank_instructions' => $bank?->getTranslation('instructions', app()->getLocale(), false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Platební karta (Stripe)')
                    ->description('Zaplatí se hned a předplatné se aktivuje samo. Klíče se dřív daly měnit jen v souboru na serveru; tady zadané mají přednost.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('stripe_enabled')
                            ->label('Nabízet platbu kartou')
                            ->columnSpanFull(),

                        TextInput::make('stripe_public_key')
                            ->label('Veřejný klíč')
                            ->placeholder('pk_live_…'),

                        TextInput::make('stripe_secret_key')
                            ->label('Tajný klíč')
                            ->password()
                            ->revealable()
                            ->placeholder('sk_live_…')
                            ->helperText('Bez něj platbu kartou nabídnout nejde.'),

                        TextInput::make('stripe_webhook_secret')
                            ->label('Podpis webhooku')
                            ->password()
                            ->revealable()
                            ->placeholder('whsec_…')
                            ->helperText('Bez něj se nedá ověřit, že oznámení o platbě přišlo opravdu od Stripe.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Bankovní převod')
                    ->description('Objednávka počká, než peníze dorazí; předplatné aktivujete v seznamu předplatných tlačítkem „Potvrdit platbu". Platnost začíná dnem potvrzení, ne dnem objednávky.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('bank_enabled')
                            ->label('Nabízet platbu převodem')
                            ->columnSpanFull(),

                        TextInput::make('bank_account_holder')
                            ->label('Majitel účtu'),

                        TextInput::make('bank_name')
                            ->label('Banka'),

                        TextInput::make('bank_account_number')
                            ->label('Číslo účtu')
                            ->placeholder('123456789/0100')
                            ->helperText('Číslo účtu nebo IBAN musí být vyplněné — jinak by kupující neměl kam poslat peníze.'),

                        TextInput::make('bank_iban')
                            ->label('IBAN')
                            ->placeholder('CZ65 0800 0000 1920 0014 5399'),

                        TextInput::make('bank_swift')
                            ->label('SWIFT / BIC'),

                        Textarea::make('bank_instructions')
                            ->label('Doplňující pokyny pro kupujícího')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Zobrazí se pod údaji k platbě. Číslo účtu ani variabilní symbol sem psát nemusíte, ty se doplní samy.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        if (! PaymentMethods::ready()) {
            Notification::make()
                ->title('Uložit zatím nejde')
                ->body('Chybí databázová tabulka. Spusťte na serveru `php artisan migrate`.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        PaymentMethods::sync();

        $stripe = PaymentMethods::find(PaymentMethod::CODE_STRIPE);
        $stripe?->forceFill([
            'is_enabled' => (bool) ($data['stripe_enabled'] ?? false),
            'settings' => [
                'public_key' => trim((string) ($data['stripe_public_key'] ?? '')),
                'secret_key' => trim((string) ($data['stripe_secret_key'] ?? '')),
                'webhook_secret' => trim((string) ($data['stripe_webhook_secret'] ?? '')),
            ],
        ])->save();

        $bank = PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER);
        $bank?->forceFill([
            'is_enabled' => (bool) ($data['bank_enabled'] ?? false),
            'settings' => [
                'account_holder' => trim((string) ($data['bank_account_holder'] ?? '')),
                'account_number' => trim((string) ($data['bank_account_number'] ?? '')),
                'bank_name' => trim((string) ($data['bank_name'] ?? '')),
                'iban' => trim((string) ($data['bank_iban'] ?? '')),
                'swift' => trim((string) ($data['bank_swift'] ?? '')),
            ],
        ]);

        $bank?->setTranslation('instructions', app()->getLocale(), (string) ($data['bank_instructions'] ?? ''));
        $bank?->save();

        // Zapnutá metoda bez údajů je horší než vypnutá: nabízí zákazníkovi
        // cestu, která nikam nevede. Proto se to řekne hned tady.
        $unusable = PaymentMethod::query()
            ->enabled()
            ->get()
            ->filter(fn (PaymentMethod $method) => ! $method->isUsable());

        if ($unusable->isNotEmpty()) {
            Notification::make()
                ->title('Uloženo, ale některé metody nejdou použít')
                ->body($unusable
                    ->map(fn (PaymentMethod $method) => PaymentMethods::label($method->code) . ': ' . $method->unusableReason())
                    ->implode(' '))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()->title('Platební metody uloženy')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Uložit')->submit('save'),
        ];
    }
}
