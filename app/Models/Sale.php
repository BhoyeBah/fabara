<?php
namespace App\Models;

use App\Support\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    protected $guarded = [];
    protected $casts = ['subtotal' => 'float', 'discount' => 'float', 'total' => 'float', 'paid' => 'float', 'tax' => 'float', 'cancelled_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Sale $s) {
            if (! $s->number) {
                // Prochain numéro libre du jour (reste correct même si des ventes ont été supprimées).
                $prefix = 'F-' . now()->format('ymd') . '-';
                $n = static::where('number', 'like', $prefix . '%')->count() + 1;
                while (static::where('number', $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT))->exists()) {
                    $n++;
                }
                $s->number = $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
            }
            $s->user_id ??= auth()->id();
            $s->discount ??= 0;
        });
    }

    public function scopeActive($q) { return $q->whereNull('cancelled_at'); }

    public function items() { return $this->hasMany(SaleItem::class); }
    public function seller() { return $this->belongsTo(User::class, 'user_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }

    public function getBalanceAttribute(): float
    {
        return $this->cancelled_at ? 0.0 : max(0, (float) $this->total - (float) $this->paid);
    }

    /** À appeler une fois les lignes enregistrées : totaux, TVA incluse, et baisse du stock. */
    public function finalize(): void
    {
        DB::transaction(function () {
            $this->load('items');
            // Règle : seul l'administrateur fixe ou modifie les prix et accorde des remises.
            $role = auth()->user()?->role?->value;
            if ($role && $role !== 'admin') {
                foreach ($this->items as $i) {
                    $p = $i->product_id ? Product::find($i->product_id) : null;
                    if ($p && $p->price) {
                        $i->update(['unit_price' => $p->price, 'cost_price' => $p->cost_price]);
                    }
                }
                $this->discount = 0;
            }
            $sub = $this->items->sum(fn ($i) => $i->quantity * $i->unit_price);
            $total = max(0, $sub - (float) $this->discount);
            $paid = $this->paid === null ? $total : min((float) $this->paid, $total);
            $rate = (float) Setting::get('tax_rate');
            $tax = $rate > 0 ? round($total - $total / (1 + $rate / 100), 2) : 0;
            $this->update(['subtotal' => $sub, 'total' => $total, 'paid' => $paid, 'tax' => $tax]);
            foreach ($this->items as $i) {
                if ($i->product_id && ($p = Product::find($i->product_id))) {
                    StockMovement::move($p, -$i->quantity, 'vente', $this->number, $this->id);
                }
            }
        });
    }

    /** Annule la facture : le stock revient, les chiffres l'ignorent. */
    public function cancel(string $reason): void
    {
        if ($this->cancelled_at) {
            return;
        }
        DB::transaction(function () use ($reason) {
            foreach ($this->items()->get() as $i) {
                if ($i->product_id && ($p = Product::find($i->product_id))) {
                    StockMovement::move($p, $i->quantity, 'retour', 'Annulation ' . $this->number, $this->id);
                }
            }
            $this->update(['cancelled_at' => now(), 'cancel_reason' => $reason]);
        });
    }
}
