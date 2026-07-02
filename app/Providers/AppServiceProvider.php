<?php

namespace App\Providers;

use App\Models\Page;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production/staging environments
        if ($this->app->environment('production', 'staging')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Configure language switch
        $languageSwitch = LanguageSwitch::make()
            ->locales(['en', 'cs'])
            ->labels([
                'en' => 'English',
                'cs' => 'Čeština',
            ])
            ->flags([
                'en' => 'https://flagcdn.com/w20/gb.png',
                'cs' => 'https://flagcdn.com/w20/cz.png',
            ])
            ->displayLocale('cs')
            ->visible(insidePanels: true, outsidePanels: false)
            ->renderHook('panels::global-search.after');

        // Store the configured instance in the container
        $this->app->instance(LanguageSwitch::class, $languageSwitch);

        // Define gate for admin panel access
        Gate::define('access-filament-admin', function ($user) {
            return $user->hasRole('admin');
        });

        // Share navigation pages with all views
        View::composer('components.navbar', function ($view) {
            $pages = Page::where('display_in_menu', true)
                ->where('is_published', true)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $view->with('navPages', $pages);
        });

        // Share footer pages with footer component
        View::composer('components.footer', function ($view) {
            $pages = Page::where('display_in_footer', true)
                ->where('is_published', true)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $view->with('footerPages', $pages);
        });
    }
}
