<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\Translation;
use App\Support\Locales;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * The 18+ gate: whether it stands, and what it says.
 *
 * It is a legal notice, so the operator has to be able to change the wording
 * without a deploy — and to switch the whole thing off, which is why the flag
 * lives next to the text rather than in a config file.
 */
class ManageAgeGate extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?int $navigationSort = 23;

    protected string $view = 'filament.pages.manage-age-gate';

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * Key => [label, is long text], in the order the gate draws them.
     *
     * @var array<string, array{0: string, 1: bool}>
     */
    private const FIELDS = [
        'numeral' => ['Velké číslo', false],
        'heading' => ['Nadpis', false],
        'brand' => ['Název webu (tučně na začátku textu)', false],
        'body' => ['Právní text', true],
        'agreement' => ['Závěrečná věta (tučně)', true],
        'enter' => ['Tlačítko pro vstup', false],
        'leave' => ['Tlačítko pro odchod', false],
    ];

    public static function getNavigationGroup(): ?string
    {
        return 'Obsah';
    }

    public static function getNavigationLabel(): string
    {
        return 'Věková brána';
    }

    public function getTitle(): string
    {
        return 'Věková brána 18+';
    }

    public function mount(): void
    {
        $values = [
            'enabled' => Setting::getBool('age_gate_enabled', true),
            'leave_url' => (string) Setting::get('age_gate_leave_url', 'https://www.google.com'),
        ];

        foreach (Locales::codes() as $locale) {
            foreach (array_keys(self::FIELDS) as $key) {
                // The effective value: whatever the gate would print today.
                $values[$locale][$key] = (string) __('front.age_gate.' . $key, [], $locale);
            }
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Zobrazení')
                    ->description('Vypnutá brána se na web vůbec nevykreslí.')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Zobrazovat věkovou bránu')
                            ->helperText('Návštěvník ji uvidí při první návštěvě. Po vstupu se mu už neukáže — dokud nezměníte text níže.'),

                        TextInput::make('leave_url')
                            ->label('Kam vede „Odejít"')
                            ->url()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Sem odejde návštěvník, který souhlas nedá. Stejně se chová i křížek v rohu.'),
                    ]),

                Section::make('Text brány')
                    ->description('Právní text. Změna se na webu projeví okamžitě a vyžádá si nový souhlas i od návštěvníků, kteří už jednou vstoupili.')
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('locales')
                            ->columnSpanFull()
                            ->tabs(array_map(
                                fn (string $locale) => Tabs\Tab::make($locale)
                                    ->label(Locales::nativeName($locale))
                                    ->schema(array_map(
                                        fn (string $key) => self::FIELDS[$key][1]
                                            ? Textarea::make("{$locale}.{$key}")
                                                ->label(self::FIELDS[$key][0])
                                                ->rows(self::FIELDS[$key][0] === 'Právní text' ? 12 : 3)
                                                ->required()
                                                ->maxLength(4000)
                                            : TextInput::make("{$locale}.{$key}")
                                                ->label(self::FIELDS[$key][0])
                                                ->required()
                                                ->maxLength(255),
                                        array_keys(self::FIELDS)
                                    )),
                                Locales::codes()
                            )),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('age_gate_enabled', ! empty($data['enabled']) ? '1' : '0');
        Setting::set('age_gate_leave_url', trim((string) ($data['leave_url'] ?? '')));

        foreach (Locales::codes() as $locale) {
            foreach (array_keys(self::FIELDS) as $key) {
                $value = trim((string) ($data[$locale][$key] ?? ''));

                if ($value === '') {
                    continue;
                }

                // Written as an override row; the lang file stays the shipped
                // default, so it is always clear what the text used to be.
                Translation::updateOrCreate(
                    ['locale' => $locale, 'group' => 'front', 'key' => 'age_gate.' . $key],
                    ['value' => $value],
                );
            }
        }

        Translation::flushCache();

        Notification::make()
            ->title('Věková brána uložena')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Uložit')
                ->submit('save'),
        ];
    }
}
