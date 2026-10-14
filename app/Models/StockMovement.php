<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    protected $guarded = [];

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }

    /** Change le stock d'un article (+ entrée, − sortie) et garde la trace. Sans effet si stock illimité. */
    public static function move(Product $product, int $qty, string $type, ?string $note = null, ?int $saleId = null): void
    {
        DB::transaction(function () use ($product, $qty, $type, $note, $saleId) {
            $p = Product::lockForUpdate()->find($product->id);
            if (! $p || $p->stock === null || $qty === 0) {
                return;
            }
            $p->update(['stock' => max(0, $p->stock + $qty)]);
            static::create(['product_id' => $p->id, 'type' => $type, 'quantity' => $qty, 'note' => $note, 'user_id' => auth()->id(), 'sale_id' => $saleId]);
        });
    }
}
