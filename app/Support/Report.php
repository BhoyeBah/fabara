<?php
namespace App\Support;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Carbon;

/** Calculs comptables pour un mois (format "2026-10") ou pour toute la période (null). */
class Report
{
    public static function range(?string $month): array
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $from = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
            return [$from, $from->copy()->endOfMonth()];
        }
        return [Carbon::create(2000, 1, 1)->startOfDay(), now()->endOfDay()];
    }

    public static function build(?string $month): array
    {
        [$from, $to] = self::range($month);
        $sales = Sale::active()->whereBetween('created_at', [$from, $to])->with('items.product.category')->get();

        $ca = (float) $sales->sum('total');
        $tax = (float) $sales->sum('tax');
        $cost = (float) $sales->sum(fn ($s) => $s->items->sum(fn ($i) => $i->quantity * $i->cost_price));

        $byMenu = ['boutique' => 0.0, 'imprimerie' => 0.0];
        $byProduct = [];
        foreach ($sales as $s) {
            $sub = $s->items->sum(fn ($i) => $i->quantity * $i->unit_price) ?: 1;
            $k = $s->total / $sub;
            foreach ($s->items as $i) {
                $amt = $i->quantity * $i->unit_price * $k;
                $menu = $i->product?->category?->menu ?? 'imprimerie';
                $byMenu[$menu] += $amt;
                $byProduct[$i->name] = [
                    'qty' => ($byProduct[$i->name]['qty'] ?? 0) + $i->quantity,
                    'amount' => ($byProduct[$i->name]['amount'] ?? 0) + $amt,
                ];
            }
        }
        uasort($byProduct, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        $charges = Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('category, SUM(amount) as t')->groupBy('category')->pluck('t', 'category')->map(fn ($v) => (float) $v)->all();
        $chargesTotal = array_sum($charges);

        return [
            'month' => $month, 'from' => $from, 'to' => $to,
            'ca' => $ca, 'tax' => $tax, 'ht' => $ca - $tax,
            'collected' => (float) $sales->sum('paid'),
            'cost' => $cost, 'gross' => $ca - $tax - $cost,
            'charges' => $charges, 'charges_total' => $chargesTotal,
            'net' => $ca - $tax - $cost - $chargesTotal,
            'purchases' => (float) Purchase::whereBetween('purchased_on', [$from->toDateString(), $to->toDateString()])->sum('amount'),
            'supplier_debt' => (float) Purchase::whereColumn('paid', '<', 'amount')->selectRaw('COALESCE(SUM(amount - paid),0) as d')->value('d'),
            'receivables' => (float) Sale::active()->whereColumn('paid', '<', 'total')->selectRaw('COALESCE(SUM(total - paid),0) as d')->value('d'),
            'by_menu' => $byMenu,
            'by_method' => $sales->groupBy('method')->map(fn ($g) => (float) $g->sum('total'))->all(),
            'top' => array_slice($byProduct, 0, 10, true),
            'sales_count' => $sales->count(),
        ];
    }
}
