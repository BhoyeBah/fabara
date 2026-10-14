<?php
namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

/** Page de connexion : même formulaire, titre adapté à l'interface. */
class Login extends BaseLogin
{
    /** Une seule connexion pour toute l'équipe : on renvoie vers la page du site. */
    public function mount(): void
    {
        $this->redirect(route('login'));
    }

    public function getHeading(): string|Htmlable
    {
        return match (Filament::getCurrentPanel()?->getId()) {
            'admin' => 'Espace administrateur',
            'caisse' => 'Espace caisse',
            'atelier' => 'Espace imprimerie',
            default => 'Connexion',
        };
    }

    public function getSubHeading(): string|Htmlable|null
    {
        return 'Entrez votre email et votre mot de passe.';
    }
}
