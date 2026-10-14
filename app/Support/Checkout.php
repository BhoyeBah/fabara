<?php
namespace App\Support;

use App\Models\Product;
use App\Models\Sale;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Seul endroit qui crée une vente de comptoir.
 * Les prix viennent TOUJOURS du catalogue (fixés par l'administrateur) : la caisse ne les modifie jamais.
 */
class Checkout
{
    /** @param array<int,int|string> $lines [id de l'article => quantité] */
    public static function make(array $lines, string $customer = 'Client comptoir', string $method = 'especes', ?float $paid = null, float $discount = 0, ?int $userId = null): Sale
    {
        $lines = array_filter(array_map('intval', $lines), fn ($q) => $q > 0);
        if (! $lines) {
            throw new DomainException('Le panier est vide.');
        }
        if (! in_array($method, ['especes', 'mobile_money', 'virement', 'carte'], true)) {
            $method = 'especes';
        }

        return DB::transaction(function () use ($lines, $customer, $method, $paid, $discount, $userId) {
            $products = Product::whereIn('id', array_keys($lines))->lockForUpdate()->get()->keyBy('id');

            foreach ($lines as $id => $qty) {
                $p = $products->get($id);
                if (! $p || ! $p->is_active) {
                    throw new DomainException('Un article du panier n’existe plus.');
                }
                if (! $p->price || $p->price <= 0) {
                    throw new DomainException('« ' . $p->name . ' » n’a pas de prix : l’administrateur doit le fixer.');
                }
                if ($p->stock !== null && $qty > $p->stock) {
                    throw new DomainException('Stock insuffisant pour « ' . $p->name . ' » (il en reste ' . $p->stock . ').');
                }
            }

            $sale = Sale::create([
                'customer_name' => trim($customer) !== '' ? trim($customer) : 'Client comptoir',
                'method' => $method,
                'discount' => max(0, $discount),
                'paid' => $paid,
                'user_id' => $userId ?? auth()->id(),
            ]);
            foreach ($lines as $id => $qty) {
                $p = $products->get($id);
                $sale->items()->create([
                    'product_id' => $p->id, 'name' => $p->name, 'quantity' => $qty,
                    'unit_price' => $p->price, 'cost_price' => $p->cost_price ?? 0,
                ]);
            }
            $sale->finalize();

            return $sale->fresh('items');
        });
    }
}
