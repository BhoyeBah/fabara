<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function show(Sale $sale)
    {
        abort_unless(Auth::user()->is_active, 403);
        $sale->load('items', 'seller');
        return Pdf::loadView('pdf.invoice', ['sale' => $sale])->stream($sale->number . '.pdf');
    }

    /** Reçu de caisse (ticket 80 mm) : page web imprimable, sans dépendre du moteur PDF. */
    public function receipt(Request $r, Sale $sale)
    {
        abort_unless(Auth::user()->is_active, 403);
        $sale->load('items', 'seller');
        $back = (string) $r->query('back', '');
        $back = ($back !== '' && str_starts_with($back, url('/'))) ? $back : null;
        return view('receipt.ticket', ['sale' => $sale, 'back' => $back]);
    }
}
