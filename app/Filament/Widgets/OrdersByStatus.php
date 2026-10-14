<?php
namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\PrintOrder;

class OrdersByStatus extends FabaraChart
{
    protected static array $roles = ['admin', 'agent'];
    protected static ?string $heading = 'Commandes d’imprimerie par étape';

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->doughnutOptions(false); }

    protected function getData(): array
    {
        $counts = PrintOrder::query()->toBase()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        $labels = $data = [];
        foreach (OrderStatus::cases() as $s) {
            $labels[] = $s->getLabel();
            $data[] = (int) ($counts[$s->value] ?? 0);
        }
        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data, 'backgroundColor' => [self::GRAY, self::YELLOW, self::BLUE, self::GREEN],
                'borderWidth' => 3, 'borderColor' => '#ffffff', 'hoverOffset' => 6,
            ]],
        ];
    }
}
