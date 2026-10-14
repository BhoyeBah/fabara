<?php
namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

abstract class BasePanelProvider extends PanelProvider
{
    protected function base(Panel $panel, string $id, string $path, array|string $color = 'Red'): Panel
    {
        return $panel
            ->id($id)->path($path)->login(Login::class)->darkMode(false)
            ->brandName('Fabara')->brandLogo(asset('images/logo.jpg'))->brandLogoHeight('2.75rem')->favicon(asset('images/logo.jpg'))
            ->colors(['primary' => is_array($color) ? $color : constant(Color::class . '::' . $color), 'gray' => Color::Neutral])
            // Navigation sans rechargement complet de la page : l'interface reste fluide.
            ->spa()
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->navigationGroups(['Ventes', 'Imprimerie', 'Catalogue et stock', 'Achats et finances', 'Administration'])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.theme'))
            ->pages([Dashboard::class])
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class, AuthenticateSession::class,
                ShareErrorsFromSession::class, VerifyCsrfToken::class, SubstituteBindings::class,
                DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
