<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\RatingScale;
use BackedEnum;
use Filament\Actions\Action;
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
