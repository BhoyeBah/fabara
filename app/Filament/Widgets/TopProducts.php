<?php
namespace App\Filament\Widgets;

use App\Models\SaleItem;

class TopProducts extends FabaraChart
{
    protected static array $roles = ['admin', 'gerante'];
    protected static ?string $heading = 'Articles les plus vendus';
    protected int|string|array $columnSpan = 1;
    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return ['7' => '7 derniers jours', '30' => '30 derniers jours', '90' => '3 derniers mois'];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): \Filament\Support\RawJs { return $this->barOptions(true, true); }

    protected function getData(): array
    {
        $from = today()->subDays(max(1, (int) $this->filter) - 1);
        $rows = SaleItem::query()->toBase()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.cancelled_at')->where('sales.created_at', '>=', $from)
            ->selectRaw('sale_items.name as n, SUM(sale_items.quantity * sale_items.unit_price) as a')
            ->groupBy('sale_items.name')->orderByDesc('a')->limit(8)->get();

        return [
            'labels' => $rows->map(fn ($r) => mb_strimwidth($r->n, 0, 28, '…'))->all(),
            'datasets' => [[
                'label' => 'Chiffre d’affaires', 'data' => $rows->map(fn ($r) => round((float) $r->a))->all(),
                'backgroundColor' => $this->alpha(self::PINK, .85), 'borderRadius' => 8, 'borderSkipped' => false, 'maxBarThickness' => 26,
            ]],
        ];
    }
}
