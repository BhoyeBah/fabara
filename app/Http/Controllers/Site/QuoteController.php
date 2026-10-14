<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function store(Request $r)
    {
        if ($r->filled('website')) {          // champ piège pour les robots
            return back();
        }
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'subject' => 'required|string|max:150',
            'details' => 'nullable|string|max:1000',
        ]);
        QuoteRequest::create($d);
        return redirect(url('/') . '#devis')->with('success', 'Votre demande a bien été envoyée. Nous vous recontactons très vite.');
    }
}
