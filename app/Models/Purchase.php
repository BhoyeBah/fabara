<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $guarded = [];
    protected $casts = ['purchased_on' => 'date', 'due_date' => 'date', 'amount' => 'float', 'paid' => 'float'];

    protected static function booted(): void
    {
        // Un achat lié à un article (avec quantité) fait entrer la marchandise en stock.
        static::created(function (Purchase $p) {
            if ($p->product_id && $p->quantity && ($prod = Product::find($p->product_id))) {
                StockMovement::move($prod, (int) $p->quantity, 'achat', $p->label);
            }
        });
    }

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->amount - $this->paid);
    }
}
