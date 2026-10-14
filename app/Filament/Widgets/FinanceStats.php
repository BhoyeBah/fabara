<?php
namespace App\Filament\Widgets;

use App\Support\Money;
use App\Support\Report;
use Filament\Widgets\StatsOverviewWidget as Base;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStats extends Base
{
    public static function canView(): bool { return auth()->user()?->role?->value === 'admin'; }

    protected function getStats(): array
    {
        $r = Report::build(now()->format('Y-m'));
        return [
            Stat::make('Chiffre d’affaires du mois', Money::fmt($r['ca'])),
            Stat::make('Encaissé ce mois', Money::fmt($r['collected'])),
            Stat::make('Créances clients', Money::fmt($r['receivables']))->color($r['receivables'] > 0 ? 'danger' : 'success'),
            Stat::make('Dettes fournisseurs', Money::fmt($r['supplier_debt']))->color($r['supplier_debt'] > 0 ? 'warning' : 'success'),
            Stat::make('Bénéfice net du mois', Money::fmt($r['net']))
                ->description('CA hors taxe − coût des articles − charges')->color($r['net'] >= 0 ? 'success' : 'danger'),
        ];
    }
}
