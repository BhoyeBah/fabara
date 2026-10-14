<?php
namespace App\Providers\Filament;

use App\Filament\Pages;
use App\Filament\Resources;
use App\Filament\Widgets;
use Filament\Panel;

/** Espace de la gérante : /caisse */
class CaissePanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->base($panel, 'caisse', 'caisse', 'Pink')
            ->pages([Pages\Pos::class])
            ->resources([
                Resources\SaleResource::class, Resources\QuoteRequestResource::class, Resources\ProductResource::class,
                Resources\CustomerResource::class,
            ])
            ->widgets([
                Widgets\Alerts::class, Widgets\TodayStats::class,
                Widgets\SalesTrend::class, Widgets\SalesByMenu::class, Widgets\PaymentMethods::class, Widgets\TopProducts::class,
                Widgets\OrdersByStatus::class, Widgets\OrdersDue::class, Widgets\ResultByMonth::class,
            ]);
    }
}
