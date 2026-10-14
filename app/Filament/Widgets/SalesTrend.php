<?php
namespace App\Filament\Widgets;

use App\Models\Sale;

class SalesTrend extends FabaraChart
{
    protected static array $roles = ['admin', 'gerante'];
    protected static ?string $heading = 'Ventes par jour';
    protected int|string|array $columnSpan = 'full';
    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return ['7' => '7 derniers jours', '30' => '30 derniers jours', '90' => '3 derniers mois'];
    }

    protected function getType(): string { return 'line'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->lineOptions(); }

    protected function getData(): array
    {
        $days = max(1, (int) $this->filter);
        $from = today()->subDays($days - 1);
        $rows = Sale::active()->where('created_at', '>=', $from)->get(['created_at', 'total', 'paid'])
            ->groupBy(fn ($sale) => $sale->created_at->format('Y-m-d'));

        $labels = $sales = $paid = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i);
            $g = $rows->get($d->format('Y-m-d'), collect());
            $labels[] = $d->format('d/m');
            $sales[] = (float) $g->sum('total');
            $paid[] = (float) $g->sum('paid');
        }
        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Ventes', 'data' => $sales, 'borderColor' => self::RED, 'backgroundColor' => $this->alpha(self::RED, .12), 'fill' => true, 'tension' => .35],
                ['label' => 'Encaissé', 'data' => $paid, 'borderColor' => self::GREEN, 'backgroundColor' => 'transparent', 'fill' => false, 'tension' => .35, 'borderDash' => [6, 4]],
            ],
        ];
    }
}
