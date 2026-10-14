<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PrintOrder;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function form()
    {
        return view('site.track', ['order' => null, 'searched' => false]);
    }

    public function lookup(Request $r)
    {
        $d = $r->validate(['code' => 'required|string|max:10', 'phone' => 'required|string|max:30']);
        $order = PrintOrder::where('code', strtoupper(trim($d['code'])))->first();
        $last8 = fn ($p) => substr(preg_replace('/\D/', '', (string) $p), -8);
        if ($order && ($last8($order->phone) === '' || $last8($order->phone) !== $last8($d['phone']))) {
            $order = null;      // le code ET le téléphone doivent correspondre
        }
        return view('site.track', ['order' => $order, 'searched' => true]);
    }
}
