<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $guarded = [];

    public function printOrder() { return $this->belongsTo(PrintOrder::class); }

    /** Transforme la demande du client en commande d'imprimerie. */
    public function toPrintOrder(): PrintOrder
    {
        $order = PrintOrder::create([
            'customer_name' => $this->name, 'phone' => $this->phone, 'title' => $this->subject, 'notes' => $this->details,
        ]);
        $this->update(['status' => 'traitee', 'print_order_id' => $order->id]);
        return $order;
    }
}
