<?php
namespace App\Providers\Filament;

use App\Filament\Resources;
use App\Filament\Widgets;
use Filament\Panel;

/** Espace des agents de l'imprimerie : /atelier */
class AtelierPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->base($panel, 'atelier', 'atelier', 'Emerald')
            ->resources([Resources\PrintOrderResource::class, Resources\QuoteRequestResource::class, Resources\CustomerResource::class])
            ->widgets([
                Widgets\Alerts::class, Widgets\TodayStats::class,
                Widgets\SalesTrend::class, Widgets\SalesByMenu::class, Widgets\PaymentMethods::class, Widgets\TopProducts::class,
                Widgets\OrdersByStatus::class, Widgets\OrdersDue::class, Widgets\ResultByMonth::class,
            ]);
    }
}
