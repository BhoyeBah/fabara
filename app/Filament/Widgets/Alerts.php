<?php
namespace App\Filament\Widgets;

use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as Base;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Alertes du jour, adaptées au rôle de la personne connectée. */
class Alerts extends Base
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $role = auth()->user()?->role?->value;
        $stats = [];
        if (in_array($role, ['admin', 'gerante'], true)) {
            $low = Product::where('is_active', true)->whereNotNull('stock')->whereColumn('stock', '<=', 'min_stock')->count();
            $stats[] = Stat::make('Articles en stock bas', $low)->color($low ? 'danger' : 'success');
        }
        if (in_array($role, ['admin', 'agent'], true)) {
            $late = PrintOrder::whereNotNull('due_date')->whereDate('due_date', '<', today())->where('status', '!=', 'livree')->count();
            $stats[] = Stat::make('Commandes en retard', $late)->color($late ? 'danger' : 'success');
            if ($role === 'admin') {
                $noPrice = PrintOrder::where('price', '<=', 0)->where('status', '!=', 'livree')->count();
                $stats[] = Stat::make('Commandes sans prix (à fixer)', $noPrice)->color($noPrice ? 'warning' : 'success');
            }
        }
        $req = QuoteRequest::where('status', 'nouvelle')->count();
        $stats[] = Stat::make('Nouvelles demandes clients', $req)->color($req ? 'warning' : 'success');
        if (in_array($role, ['admin', 'gerante'], true)) {
            $unpaid = Sale::active()->whereColumn('paid', '<', 'total')->count();
            $stats[] = Stat::make('Ventes avec reste à payer', $unpaid)->color($unpaid ? 'warning' : 'success');
        }
        return $stats;
    }
}
