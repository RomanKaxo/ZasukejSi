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
            ])->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ]);
    }
}
