<?php
namespace App\Http\Controllers\Site;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/** Connexion unique : chaque identifiant mène à son interface (admin, caisse, atelier ou espace client). */
class AuthController extends Controller
{
    public function form()
    {
        return view('site.login');
    }

    public function login(Request $r)
    {
        $c = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($c + ['is_active' => true], $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect(Auth::user()->role->home());
        }
        return back()->withErrors(['email' => 'Identifiants incorrects ou compte désactivé.'])->onlyInput('email');
    }

    public function registerForm()
    {
        return Auth::check() ? redirect(Auth::user()->role->home()) : view('site.register');
    }

    public function register(Request $r)
    {
        if ($r->filled('website')) {      // champ piège anti-robots
            return redirect()->route('home');
        }
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => 'accepted',
        ], ['email.unique' => 'Un compte existe déjà avec cet email.', 'terms.accepted' => 'Vous devez accepter les conditions et la politique de confidentialité.']);
        $u = User::create([
            'name' => $d['name'], 'email' => $d['email'], 'phone' => $d['phone'] ?? null,
            'password' => $d['password'], 'role' => Role::Client, 'is_active' => true, 'terms_accepted_at' => now(),
        ]);
        Auth::login($u);
        $r->session()->regenerate();
        return redirect('/mon-compte')->with('status', 'Votre compte est créé. Bienvenue !');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
