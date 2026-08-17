<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('ZAŠUKEJSI.CZ')
            ->brandLogo(fn () => view('components.admin-logo'))
            ->darkModeBrandLogo(fn () => view('components.admin-logo'))
            ->font('Poppins')
            ->colors([
                'primary' => [
                    50 => '253, 242, 248',
                    100 => '252, 231, 243',
                    200 => '249, 208, 231',
                    300 => '244, 176, 213',
                    400 => '236, 116, 174',
                    500 => '221, 56, 136',
                    600 => '190, 24, 93',
                    700 => '157, 23, 77',
                    800 => '131, 25, 67',
                    900 => '112, 26, 60',
                    950 => '69, 10, 33',
                ],
                'secondary' => [
                    50 => '248, 246, 248',
                    100 => '238, 234, 239',
                    200 => '221, 213, 223',
                    300 => '196, 183, 199',
                    400 => '162, 144, 166',
                    500 => '92, 45, 98',
                    600 => '76, 37, 81',
                    700 => '62, 30, 66',
                    800 => '52, 26, 55',
                    900 => '45, 22, 47',
                    950 => '25, 12, 27',
                ],
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureUserIsAdmin::class,
            ])
            // Photo thumbnails four to five across instead of stacked one per
            // row, which made the profile form scroll for a screen and a half.
            // Injected here because the panel has no compiled theme of its own.
            // Legenda k sekci. Jedna registrace místo úpravy sedmadvaceti
            // tříd stránek — obsah se bere podle adresy, takže nová sekce
            // dostane legendu přidáním záznamu do App\Support\AdminGuides.
            ->renderHook(
                \Filament\View\PanelsRenderHook::CONTENT_START,
                fn (): string => view('filament.section-guide')->render(),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => <<<'HTML'
                    <style>
                        /* Legenda k sekci. Vlastní CSS, protože panel nemá
                           zkompilovaný theme — Tailwind utility napsané
                           v Blade šabloně by v jeho stylech nebyly. */
                        .zs-guide {
                            margin-bottom: 1.5rem;
                            border-radius: 0.75rem;
                            background: #FFFFFF;
                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(9, 9, 11, 0.05);
                            overflow: hidden;
                        }
                        .dark .zs-guide {
                            background: #18181B;
                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
                        }
                        .zs-guide__head {
                            display: flex;
                            align-items: center;
                            gap: 0.75rem;
                            width: 100%;
                            padding: 0.75rem 1rem;
                            text-align: left;
                            background: none;
                            border: 0;
                            cursor: pointer;
                        }
                        .zs-guide__mark {
                            flex: 0 0 auto;
                            width: 20px;
                            height: 20px;
                            border-radius: 999px;
                            border: 1.5px solid #DD3888;
                            color: #DD3888;
                            font-size: 12px;
                            font-weight: 700;
                            font-style: italic;
                            line-height: 17px;
                            text-align: center;
                        }
                        .zs-guide__title {
                            font-size: 0.875rem;
                            font-weight: 600;
                            color: #18181B;
                        }
                        .dark .zs-guide__title { color: #FFFFFF; }
                        .zs-guide__chevron {
                            margin-left: auto;
                            font-size: 1rem;
                            line-height: 1;
                            color: #A1A1AA;
                            transition: transform 150ms ease;
                        }
                        .zs-guide__chevron.is-open { transform: rotate(180deg); }
                        .zs-guide__body { padding: 0 1rem 1rem 2.75rem; }
                        .zs-guide__intro {
                            margin: 0;
                            max-width: 62ch;
                            font-size: 0.875rem;
                            line-height: 1.6;
                            color: #52525B;
                        }
                        .dark .zs-guide__intro,
                        .dark .zs-guide__list { color: #D4D4D8; }
                        .zs-guide__label {
                            margin: 0.9rem 0 0.3rem;
                            font-size: 0.6875rem;
                            font-weight: 600;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            color: #A1A1AA;
                        }
                        .zs-guide__list {
                            margin: 0;
                            padding: 0;
                            list-style: none;
                            max-width: 62ch;
                            font-size: 0.875rem;
                            line-height: 1.6;
                            color: #52525B;
                        }
                        .zs-guide__list li {
                            display: flex;
                            gap: 0.5rem;
                            margin-bottom: 0.25rem;
                        }
                        .zs-guide__yes { color: #1B6E3C; flex: 0 0 auto; }
                        .zs-guide__no { color: #B3261E; flex: 0 0 auto; }
                        .zs-guide__links {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 0.5rem;
                        }
                        .zs-guide__links a {
                            padding: 0.2rem 0.55rem;
                            border-radius: 0.5rem;
                            background: #FBEAF2;
                            color: #C42A76;
                            font-size: 0.8125rem;
                            text-decoration: none;
                        }
                        .zs-guide__links a:hover { background: #F7D9E8; }
                        .dark .zs-guide__links a { background: #3A1E2E; color: #F06BAA; }

                        /* FilePond neskládá mřížku CSS gridem — položky jsou
                           absolutně pozicované a počet sloupců si dopočítá ze
                           šířky jedné položky. Předchozí `display: grid` na
                           seznamu proto nedělal nic a zůstávaly tři na řádek.
                           Šířka položky je jediná páka, která zabírá. */
                        .profile-media-grid .filepond--item {
                            width: calc(20% - 0.5em);
                        }

                        @media (max-width: 1279px) {
                            .profile-media-grid .filepond--item {
                                width: calc(25% - 0.5em);
                            }
                        }

                        @media (max-width: 767px) {
                            .profile-media-grid .filepond--item {
                                width: calc(50% - 0.5em);
                            }
                        }
                    </style>
                    HTML,
            )
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ]);
    }
}
