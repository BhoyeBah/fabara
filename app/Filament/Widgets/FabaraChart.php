<?php
namespace App\Filament\Widgets;

use App\Support\Setting;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

/** Base des graphiques : mêmes couleurs, mêmes infobulles, aucun rafraîchissement automatique. */
abstract class FabaraChart extends ChartWidget
{
    /** Rôles qui voient ce graphique. */
    protected static array $roles = ['admin'];
    protected static ?string $pollingInterval = null;
    protected static ?string $maxHeight = '300px';
    protected int|string|array $columnSpan = 1;

    public const RED = '#d7141a';
    public const PINK = '#d6196b';
    public const GREEN = '#0a8f3c';
    public const YELLOW = '#f6c400';
    public const BLUE = '#2563eb';
    public const GRAY = '#9ca3af';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role?->value, static::$roles, true);
    }

    /** Couleur avec transparence (#rrggbb → rgba). */
    protected function alpha(string $hex, float $a): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
        return "rgba($r,$g,$b,$a)";
    }

    /**
     * Unité (devise) sous forme de texte JavaScript entre APOSTROPHES.
     * Jamais de guillemets doubles : le code est placé dans un attribut HTML (x-data="...") et les casserait.
     */
    private function unit(bool $money): string
    {
        $c = $money ? trim((string) Setting::get('currency')) : '';
        $c = $c === '' ? '' : ' ' . $c;
        return "'" . str_replace(["\\", "'", '"', "\n", "\r"], ["\\\\", "\\'", '', ' ', ' '], $c) . "'";
    }

    protected function lineOptions(): RawJs
    {
        $u = $this->unit(true);
        return RawJs::make(<<<JS
        {
            responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                tooltip: { callbacks: { label: (c) => ' ' + c.dataset.label + ' : ' + new Intl.NumberFormat('fr-FR').format(c.parsed.y) + $u } },
            },
            elements: { line: { borderWidth: 3 }, point: { radius: 0, hoverRadius: 5 } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f1f1f4' }, border: { display: false }, ticks: { callback: (v) => new Intl.NumberFormat('fr-FR').format(v) } },
            },
        }
        JS);
    }

    protected function barOptions(bool $horizontal = false, bool $money = true, bool $legend = false): RawJs
    {
        $u = $this->unit($money);
        $axis = $horizontal ? 'x' : 'y';
        $index = $horizontal ? "indexAxis: 'y'," : '';
        $legendJs = $legend ? "legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } }," : 'legend: { display: false },';
        $grid = $horizontal
            ? "x: { beginAtZero: true, grid: { color: '#f1f1f4' }, border: { display: false }, ticks: { precision: 0, callback: (v) => new Intl.NumberFormat('fr-FR').format(v) } }, y: { grid: { display: false } }"
            : "x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#f1f1f4' }, border: { display: false }, ticks: { precision: 0, callback: (v) => new Intl.NumberFormat('fr-FR').format(v) } }";
        return RawJs::make(<<<JS
        {
            $index
            responsive: true, maintainAspectRatio: false,
            plugins: {
                $legendJs
                tooltip: { callbacks: { label: (c) => ' ' + (c.dataset.label ? c.dataset.label + ' : ' : '') + new Intl.NumberFormat('fr-FR').format(c.parsed.$axis) + $u } },
            },
            scales: { $grid },
        }
        JS);
    }

    protected function doughnutOptions(bool $money = true): RawJs
    {
        $u = $this->unit($money);
        return RawJs::make(<<<JS
        {
            responsive: true, maintainAspectRatio: false, cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                tooltip: { callbacks: { label: (c) => ' ' + c.label + ' : ' + new Intl.NumberFormat('fr-FR').format(c.parsed) + $u } },
            },
            scales: { x: { display: false }, y: { display: false } },
        }
        JS);
    }
}
