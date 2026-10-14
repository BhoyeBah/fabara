<?php
namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Ventes des 14 derniers jours';

    public static function canView(): bool { return auth()->user()?->role?->value === 'admin'; }

    protected function getType(): string { return 'line'; }

    protected function getData(): array
    {
        $labels = [];
        $data = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $labels[] = $d->format('d/m');
            $data[] = (float) Sale::active()->whereDate('created_at', $d)->sum('total');
        }
        return ['datasets' => [['label' => 'Ventes', 'data' => $data]], 'labels' => $labels];
    }
}
