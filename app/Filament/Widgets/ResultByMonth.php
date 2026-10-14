<?php
namespace App\Filament\Widgets;

use App\Support\Report;

class ResultByMonth extends FabaraChart
{
    protected static ?string $heading = 'Ventes, charges et bénéfice (6 derniers mois)';
    protected int|string|array $columnSpan = 'full';

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->barOptions(false, true, true); }

    protected function getData(): array
    {
        $labels = $ca = $charges = $net = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $r = Report::build($m->format('Y-m'));
            $labels[] = ucfirst($m->translatedFormat('M Y'));
            $ca[] = round($r['ca']);
            $charges[] = round($r['charges_total']);
            $net[] = round($r['net']);
        }
        $bar = ['borderRadius' => 8, 'borderSkipped' => false, 'maxBarThickness' => 34];
        return [
            'labels' => $labels,
            'datasets' => [
                $bar + ['label' => 'Ventes', 'data' => $ca, 'backgroundColor' => self::RED],
                $bar + ['label' => 'Charges', 'data' => $charges, 'backgroundColor' => self::YELLOW],
                $bar + ['label' => 'Bénéfice net', 'data' => $net, 'backgroundColor' => self::GREEN],
            ],
        ];
    }
}
