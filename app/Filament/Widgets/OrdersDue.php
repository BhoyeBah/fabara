<?php
namespace App\Filament\Widgets;

use App\Models\PrintOrder;

class OrdersDue extends FabaraChart
{
    protected static array $roles = ['admin', 'agent'];
    protected static ?string $heading = 'Commandes à rendre ces 7 prochains jours';

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->barOptions(false, false); }

    protected function getData(): array
    {
        $rows = PrintOrder::whereNotNull('due_date')->where('status', '!=', 'livree')
            ->whereDate('due_date', '>=', today())->whereDate('due_date', '<=', today()->addDays(6))
            ->get(['due_date'])->groupBy(fn ($o) => $o->due_date->format('Y-m-d'));

        $labels = $data = $colors = [];
        for ($i = 0; $i < 7; $i++) {
            $d = today()->addDays($i);
            $labels[] = $i === 0 ? 'Aujourd’hui' : ($i === 1 ? 'Demain' : ucfirst($d->translatedFormat('D j')));
            $data[] = $rows->get($d->format('Y-m-d'), collect())->count();
            $colors[] = $i === 0 ? self::RED : self::GREEN;
        }
        return [
            'labels' => $labels,
            'datasets' => [['label' => 'Commandes', 'data' => $data, 'backgroundColor' => $colors, 'borderRadius' => 8, 'borderSkipped' => false, 'maxBarThickness' => 38]],
        ];
    }
}
