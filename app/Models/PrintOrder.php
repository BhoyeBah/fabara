<?php
namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrintOrder extends Model
{
    protected $guarded = [];
    protected $casts = ['status' => OrderStatus::class, 'due_date' => 'date', 'price' => 'float', 'deposit' => 'float', 'material_cost' => 'float'];

    protected static function booted(): void
    {
        // Code de suivi donné au client (avec son téléphone, il peut suivre sa commande sur le site).
        static::creating(function (PrintOrder $o) {
            do {
                $code = strtoupper(Str::random(6));
            } while (static::where('code', $code)->exists());
            $o->code ??= $code;
            $o->status ??= OrderStatus::Nouvelle;
        });

        // Commande liée à un article du catalogue : le prix vient du catalogue (l'administrateur reste libre de le changer).
        static::saving(function (PrintOrder $o) {
            if ($o->product_id && ! $o->isDirty('price') && (! $o->exists || $o->isDirty('product_id'))) {
                $price = Product::whereKey($o->product_id)->value('price');
                if ($price) {
                    $o->price = (float) $price * max(1, (int) $o->quantity);
                }
            }
        });
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function agent() { return $this->belongsTo(User::class, 'user_id'); }
    public function sale() { return $this->belongsTo(Sale::class); }

    public function advance(): void
    {
        if ($n = $this->status->next()) {
            $this->update(['status' => $n]);
        }
    }

    /** Crée la facture et passe la commande à « Livrée ». */
    public function deliver(): Sale
    {
        return DB::transaction(function () {
            $sale = Sale::create([
                'customer_id' => $this->customer_id,
                'customer_name' => $this->customer_name,
                'method' => 'especes',
                'paid' => min($this->deposit, $this->price),
            ]);
            $sale->items()->create(['name' => $this->title, 'quantity' => 1, 'unit_price' => $this->price, 'cost_price' => $this->material_cost]);
            $sale->finalize();
            $this->update(['status' => OrderStatus::Livree, 'sale_id' => $sale->id]);
            return $sale;
        });
    }
}
