<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $guarded = [];
    protected $appends = ['image_url'];
    protected $casts = ['price' => 'float', 'cost_price' => 'float', 'is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            $p->cost_price = $p->cost_price ?? 0;
            $p->min_stock = $p->min_stock ?? 3;
        });
        // Un article créé avec du stock : on garde la trace du stock de départ.
        static::created(function (Product $p) {
            if ($p->stock !== null && $p->stock > 0) {
                StockMovement::create(['product_id' => $p->id, 'type' => 'ajustement', 'quantity' => $p->stock, 'note' => 'Stock initial', 'user_id' => auth()->id()]);
            }
        });
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function isLow(): bool
    {
        return $this->stock !== null && $this->stock <= $this->min_stock;
    }
}
