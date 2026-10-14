<?php
namespace App\Providers\Filament;

use App\Filament\Pages;
use App\Filament\Resources;
use App\Filament\Widgets;
use Filament\Panel;

class AdminPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->base($panel->default(), 'admin', 'admin', 'Red')
            ->pages([Pages\Pos::class, Pages\Accounting::class, Pages\Settings::class])
            ->resources([
                Resources\SaleResource::class, Resources\QuoteRequestResource::class, Resources\PrintOrderResource::class,
                Resources\ProductResource::class, Resources\StockMovementResource::class, Resources\CategoryResource::class,
                Resources\CustomerResource::class, Resources\SupplierResource::class, Resources\PurchaseResource::class,
                Resources\ExpenseResource::class, Resources\UserResource::class,
            ])
            ->widgets([
                Widgets\Alerts::class, Widgets\FinanceStats::class, Widgets\SalesChart::class,
                Widgets\SalesTrend::class, Widgets\SalesByMenu::class, Widgets\PaymentMethods::class, Widgets\TopProducts::class,
                Widgets\OrdersByStatus::class, Widgets\OrdersDue::class, Widgets\ResultByMonth::class,
            ]);
    }
}
