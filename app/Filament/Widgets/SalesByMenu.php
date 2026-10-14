<?php
namespace App\Filament\Widgets;

use App\Support\Report;

class SalesByMenu extends FabaraChart
{
    protected static ?string $heading = 'Boutique ou imprimerie ? (ce mois)';

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->doughnutOptions(); }

    protected function getData(): array
    {
        $m = Report::build(now()->format('Y-m'))['by_menu'];
        return [
            'labels' => ['Boutique', 'Imprimerie'],
            'datasets' => [[
                'data' => [round($m['boutique']), round($m['imprimerie'])],
                'backgroundColor' => [self::PINK, self::RED], 'borderWidth' => 3, 'borderColor' => '#ffffff', 'hoverOffset' => 6,
            ]],
        ];
    }
}
