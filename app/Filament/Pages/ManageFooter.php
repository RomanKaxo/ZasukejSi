<?php

namespace App\Filament\Pages;

use App\Models\Translation;
use App\Support\Locales;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * The footer's wording, per language.
 *
 * Every one of these strings was written into the Blade template. They are
 * ordinary translation keys, which the translations screen could already edit —
 * but only if you knew which keys to look for. This is the same data, gathered
 * on one screen in the order the footer draws it.
 */
class ManageFooter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?int $navigationSort = 22;

    protected string $view = 'filament.pages.manage-footer';

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * Key => label, in the order the footer renders them.
     *
     * @var array<string, string>
     */
    private const FIELDS = [
        'logo_primary' => 'Logo — první část',
        'logo_accent' => 'Logo — zvýrazněná část',
        'logo_suffix' => 'Logo — doména',
        'registration' => 'Tlačítko pro nepřihlášené',
        'discreet' => 'Text v boxu o diskrétnosti',
        'ecological' => 'Ekologický štítek',
        'verification' => 'Text vedle štítku',
        'copyright' => 'Copyright',
    ];

    public static function getNavigationGroup(): ?string
    {
        return 'Obsah';
    }

    public static function getNavigationLabel(): string
    {
        return 'Texty v patičce';
    }

    public function getTitle(): string
    {
        return 'Texty v patičce';
    }

    public function mount(): void
    {
        $values = [];

        foreach (Locales::codes() as $locale) {
            foreach (array_keys(self::FIELDS) as $key) {
                // The effective value: whatever the site would print today,
                // whether it comes from the lang file or an override.
                $values[$locale][$key] = (string) __('front.footer.' . $key, [], $locale);
            }
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Patička')
                    ->description('Změna se na webu projeví okamžitě. Prázdné pole není povolené — patička by v tom místě zůstala prázdná.')
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('locales')
                            ->columnSpanFull()
                            ->tabs(array_map(
                                fn (string $locale) => Tabs\Tab::make($locale)
                                    ->label(Locales::nativeName($locale))
                                    ->schema(array_map(
                                        fn (string $key) => TextInput::make("{$locale}.{$key}")
                                            ->label(self::FIELDS[$key])
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

        foreach (Locales::codes() as $locale) {
            foreach (array_keys(self::FIELDS) as $key) {
                $value = trim((string) ($data[$locale][$key] ?? ''));

                if ($value === '') {
                    continue;
                }

                // Written as an override row; the lang file stays the shipped
                // default, so `default_value` still says what it used to be.
                Translation::updateOrCreate(
                    ['locale' => $locale, 'group' => 'front', 'key' => 'footer.' . $key],
                    ['value' => $value],
                );
            }
        }

        Translation::flushCache();

        Notification::make()
            ->title('Texty v patičce uloženy')
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
