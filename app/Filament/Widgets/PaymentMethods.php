<?php
namespace App\Filament\Widgets;

use App\Enums\PayMethod;
use App\Support\Report;

class PaymentMethods extends FabaraChart
{
    protected static ?string $heading = 'Modes de paiement (ce mois)';

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->doughnutOptions(); }

    protected function getData(): array
    {
        $by = Report::build(now()->format('Y-m'))['by_method'];
        $labels = $data = [];
        foreach (PayMethod::cases() as $m) {
            $labels[] = $m->getLabel();
            $data[] = round($by[$m->value] ?? 0);
        }
        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data, 'backgroundColor' => [self::GREEN, self::YELLOW, self::BLUE, self::GRAY],
                'borderWidth' => 3, 'borderColor' => '#ffffff', 'hoverOffset' => 6,
            ]],
        ];
    }
}
