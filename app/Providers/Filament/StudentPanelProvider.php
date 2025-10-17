<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RoleRedirectMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
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

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->loginRouteSlug('/auth/login')
            ->colors([
                'primary' => [
                    50 => '#f0fdf4',
                    100 => '#dcfce7',
                    200 => '#bbf7d0',
                    300 => '#86efac',
                    400 => '#4ade80',
                    500 => '#16a34a',
                    600 => '#15803d',
                    700 => '#166534',
                    800 => '#14532d',
                    900 => '#052e16',
                    950 => '#052e16',
                ],
                'secondary' => [
                    50 => '#fefce8',
                    100 => '#fef9c3',
                    200 => '#fef08a',
                    300 => '#fde047',
                    400 => '#facc15',
                    500 => '#eab308',
                    600 => '#ca8a04',
                    700 => '#a16207',
                    800 => '#854d0e',
                    900 => '#713f12',
                    950 => '#422006',
                ],
            ])
            ->favicon(asset('img/logo.png'))
            ->brandName('MATAZ')
            ->brandLogo(fn () => view('filament.brand'))
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\Filament\Student\Resources')
            ->discoverPages(in: app_path('Filament/Student/Pages'), for: 'App\Filament\Student\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\Filament\Student\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->darkMode(true)
            ->darkModeBrandLogo(fn () => view('filament.brand'))
            ->renderHook('body.end', fn () => view('filament.theme-sync'))
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
                RoleRedirectMiddleware::class,
            ])->topNavigation();
    }
}
