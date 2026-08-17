<?php

namespace App\Filament\Pages;

// Aliased: this class extends Filament's own Page.
use App\Models\Page as ContentPage;
use App\Models\Setting;
use App\Support\FooterButton;
use App\Support\RatingScale;
use App\Support\TopRatedLock;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Values the operator can change without a deploy.
 *
 * Both settings here used to be magic numbers in code: the rating presets were
 * written into the member ratings blade, and the simulated online share was a
 * literal 30 inside Profile::isOnline().
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 39;

    protected string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Nastavení';
    }

    public static function getNavigationLabel(): string
    {
        return 'Nastavení systému';
    }

    public function getTitle(): string
    {
        return 'Nastavení systému';
    }

    public function mount(): void
    {
        $options = RatingScale::options();

        $this->form->fill([
            'option_high' => $options['high'],
            'option_mid' => $options['mid'],
            'option_low' => $options['low'],
            'online_simulation_percent' => Setting::getInt(
                'site.online_simulation_percent',
                (int) config('site.online_simulation_percent', 0)
            ),
            'footer_guest_page_id' => Setting::get(FooterButton::KEY_GUEST_PAGE),
            'footer_guest_label' => Setting::get(FooterButton::KEY_GUEST_LABEL),
            'footer_auth_page_id' => Setting::get(FooterButton::KEY_AUTH_PAGE)
                ?? ContentPage::published()->where("slug", FooterButton::DEFAULT_AUTH_SLUG)->value("id"),
            'footer_auth_label' => Setting::get(FooterButton::KEY_AUTH_LABEL),
            'top_rated_lock_mode' => TopRatedLock::mode(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Škála hodnocení')
                    ->description('Tři hodnoty, které členům nabízejí tlačítka na stránce hodnocení. Ukládá se procento, hvězdy jsou jen jeho zobrazení.')
                    ->schema([
                        TextInput::make('option_high')
                            ->label('Nejvyšší hodnocení (%)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),

                        TextInput::make('option_mid')
                            ->label('Střední hodnocení (%)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),

                        TextInput::make('option_low')
                            ->label('Nejnižší hodnocení (%)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100),
                    ])
                    ->columns(3),

                Section::make('Patička — tlačítko')
                    ->description('Tlačítko vlevo v patičce. Nepřihlášenému a přihlášenému lze nabídnout jinou stránku. Popisek nechte prázdný a použije se název stránky. Které odkazy patička vypisuje a v jakém pořadí, se nastavuje u jednotlivých stránek (Stránky → „Zobrazit v patičce" a „Pořadí v patičce").')
                    ->schema([
                        Select::make('footer_guest_page_id')
                            ->label('Nepřihlášený — cílová stránka')
                            ->helperText('Prázdné = otevře se okno registrace, jak je v návrhu.')
                            ->options(fn () => ContentPage::linkOptions())
                            ->searchable()
                            ->placeholder('Okno registrace'),

                        TextInput::make('footer_guest_label')
                            ->label('Nepřihlášený — popisek')
                            ->placeholder(__('front.footer.registration'))
                            ->maxLength(40),

                        Select::make('footer_auth_page_id')
                            ->label('Přihlášený — cílová stránka')
                            ->helperText('Registrovat se podruhé nelze, proto tento stav okno registrace nikdy neotevírá.')
                            ->options(fn () => ContentPage::linkOptions())
                            ->searchable()
                            ->placeholder(__('front.nav.myaccount')),

                        TextInput::make('footer_auth_label')
                            ->label('Přihlášený — popisek')
                            ->maxLength(40),
                    ])
                    ->columns(2),

                Section::make('Nejlépe hodnocené dívky')
                    ->description('Slidery v detailu profilu. Návrh rozostřuje všechny karty a bránou je Premium účet diváka; dosavadní implementace skrývá jen VIP profily. Ostré zůstávají v obou případech odznak, hodnocení, výška, věk, lokalita i tlačítko Detail — rozostřuje se fotka a jméno.')
                    ->schema([
                        Radio::make('top_rated_lock_mode')
                            ->label('Kdo uvidí karty odkryté')
                            ->options(TopRatedLock::options())
                            ->required()
                            // Inzerentky a administrátoři nejsou omezení nikdy.
                            ->helperText('Dívky a administrátoři vidí karty odkryté vždy, bez ohledu na tuto volbu.'),
                    ]),

                Section::make('Online stav')
                    ->description('Skutečná aktivita má vždy přednost. Tato hodnota řídí jen podíl ostatních profilů, které se zobrazí jako online. 0 simulaci zcela vypne.')
                    ->schema([
                        TextInput::make('online_simulation_percent')
                            ->label('Simulovaný podíl online profilů (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set(RatingScale::KEY_HIGH, RatingScale::clamp((int) $data['option_high']));
        Setting::set(RatingScale::KEY_MID, RatingScale::clamp((int) $data['option_mid']));
        Setting::set(RatingScale::KEY_LOW, RatingScale::clamp((int) $data['option_low']));
        Setting::set('site.online_simulation_percent', max(0, min(100, (int) $data['online_simulation_percent'])));

        // Stored empty rather than deleted: an empty value is what makes the
        // button fall back to its built-in behaviour.
        Setting::set(FooterButton::KEY_GUEST_PAGE, $data['footer_guest_page_id'] ?? '');
        Setting::set(FooterButton::KEY_GUEST_LABEL, $data['footer_guest_label'] ?? '');
        Setting::set(FooterButton::KEY_AUTH_PAGE, $data['footer_auth_page_id'] ?? '');
        Setting::set(FooterButton::KEY_AUTH_LABEL, $data['footer_auth_label'] ?? '');

        // Unknown value falls back to the default rather than being stored.
        $lockMode = (string) ($data['top_rated_lock_mode'] ?? TopRatedLock::DEFAULT);
        Setting::set(TopRatedLock::KEY, array_key_exists($lockMode, TopRatedLock::options())
            ? $lockMode
            : TopRatedLock::DEFAULT);

        Notification::make()
            ->title('Nastavení uloženo')
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
