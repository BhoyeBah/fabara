<?php
namespace App\Http\Controllers\Site;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Support\ClientData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Espace « Mon compte » du client : profil, commandes, export et suppression de ses données. */
class AccountController extends Controller
{
    public function show(Request $r)
    {
        $u = $r->user();
        if ($u->role !== Role::Client) {
            return view('site.account', ['u' => $u, 'staff' => true, 'quotes' => collect(), 'orders' => collect()]);
        }
        return view('site.account', ['u' => $u, 'staff' => false, 'quotes' => ClientData::quotes($u), 'orders' => ClientData::orders($u)]);
    }

    public function update(Request $r)
    {
        $this->clientOnly($r);
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($r->user()->id)],
        ]);
        $r->user()->update($d);
        return back()->with('status', 'Vos informations sont enregistrées.');
    }

    public function password(Request $r)
    {
        $this->clientOnly($r);
        $d = $r->validate(['current_password' => 'required|current_password', 'password' => ['required', 'confirmed', Password::min(8)]]);
        $r->user()->update(['password' => $d['password']]);
        return back()->with('status', 'Mot de passe modifié.');
    }

    public function export(Request $r)
    {
        $this->clientOnly($r);
        return response()->json(ClientData::export($r->user()), 200, ['Content-Disposition' => 'attachment; filename="mes-donnees-fabara.json"'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function destroy(Request $r)
    {
        $this->clientOnly($r);
        $r->validate(['password' => 'required|current_password', 'confirm' => 'accepted'], ['password.current_password' => 'Mot de passe incorrect.', 'confirm.accepted' => 'Cochez la case pour confirmer la suppression.']);
        $u = $r->user();
        Auth::logout();
        ClientData::erase($u);
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home')->with('status', 'Votre compte et vos données personnelles ont été supprimés.');
    }

    /** Page publique : comment supprimer son compte sans se connecter (exigée par Google Play). */
    public function deletionInfo()
    {
        return view('site.legal.deletion');
    }

    public function deletionRequest(Request $r)
    {
        if ($r->filled('website')) {
            return back();
        }
        $d = $r->validate(['email' => 'required|email|max:150', 'phone' => 'nullable|string|max:30']);
        QuoteRequest::create([
            'name' => 'Suppression de compte', 'phone' => $d['phone'] ?: '-', 'subject' => '🗑 Demande de suppression de compte',
            'details' => 'Email du compte : ' . $d['email'] . ' — à traiter sous 30 jours.',
        ]);
        return back()->with('status', 'Demande reçue. Nous supprimons votre compte et vos données sous 30 jours maximum.');
    }

    private function clientOnly(Request $r): void
    {
        abort_unless($r->user()->role === Role::Client, 403, 'Les comptes de l’équipe sont gérés par l’administrateur.');
    }
}
