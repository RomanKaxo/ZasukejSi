<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\User;
use App\Services\DatabaseTranslationLoader;
use App\Support\Locales;
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
        // Let the database override lang files, so every `__()` string on the
        // site becomes editable from the admin. Files stay the defaults.
        //
        // Registered by extending the container's existing binding rather than
        // replacing it, so Laravel's own path/namespace setup still applies.
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new DatabaseTranslationLoader($app['files'], $app['path.lang']);
        });
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

        // Configure language switch — driven by config/locales.php so adding a
        // language does not need a change here.
        $languageSwitch = LanguageSwitch::make()
            ->locales(Locales::codes())
            ->labels(collect(Locales::all())
                ->map(fn (array $meta) => $meta['native'])
                ->all())
            ->flags(collect(Locales::all())
                ->map(fn (array $meta, string $code) => asset($meta['flag']))
                ->all())
            ->displayLocale(Locales::source())
            ->visible(insidePanels: true, outsidePanels: false)
            ->renderHook('panels::global-search.after');

        // Store the configured instance in the container
        $this->app->instance(LanguageSwitch::class, $languageSwitch);

        // Define gate for admin panel access
        Gate::define('access-filament-admin', function ($user) {
            return $user->hasRole('admin');
        });

        // Profile ratings are behind the paid membership — the lock icon the
        // design shows on every card, and the "Premium účet vám odemkne
        // hodnocení" bar on the detail page. Guests never see them.
        Gate::define('view-ratings', function (?User $user) {
            return $user?->canSeeRatings() ?? false;
        });

        // Share navigation pages with all views
        View::composer('components.navbar', function ($view) {
            $pages = Page::where('display_in_menu', true)
                ->where('is_published', true)
                // Ordered by the admin-managed sort_order; created_at alone put
                // FAQ ahead of VIP & Premium, against the design.
                ->ordered()
                ->get();
            
            $view->with('navPages', $pages);
        });

        // Share footer pages with footer component
        View::composer('components.footer', function ($view) {
            $pages = Page::where('display_in_footer', true)
                ->where('is_published', true)
                // The footer has its own order. It used to share sort_order
                // with the header, so arranging one dragged the other along.
                ->footerOrdered()
                ->get();

            $view->with('footerPages', $pages);
        });
    }
}
