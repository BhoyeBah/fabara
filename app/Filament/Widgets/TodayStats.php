<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\PrintOrder;
use App\Models\Sale;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as Base;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayStats extends Base
{
    protected function getStats(): array
    {
        if (auth()->user()?->role?->value === 'agent') {
            return array_map(
                fn (OrderStatus $s) => Stat::make($s->getLabel(), PrintOrder::where('status', $s->value)->count()),
                [OrderStatus::Nouvelle, OrderStatus::EnCours, OrderStatus::Prete]
            );
        }
        $today = Sale::active()->whereDate('created_at', today());
        return [
            Stat::make('Ventes aujourd’hui', (clone $today)->count()),
            Stat::make('Total encaissé aujourd’hui', Money::fmt((clone $today)->sum('paid'))),
            Stat::make('Reste à encaisser (aujourd’hui)', Money::fmt((clone $today)->selectRaw('COALESCE(SUM(total - paid),0) as d')->value('d'))),
        ];
    }
}
