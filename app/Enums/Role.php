<?php
namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case Admin = 'admin';
    case Gerante = 'gerante';
    case Agent = 'agent';
    case Client = 'client';

    public function getLabel(): ?string
    {
        return match ($this) { self::Admin => 'Administrateur', self::Gerante => 'Gérante (boutique)', self::Agent => 'Agent (imprimerie)', self::Client => 'Client (site)' };
    }

    public function home(): string
    {
        return match ($this) { self::Admin => '/admin', self::Gerante => '/caisse', self::Agent => '/atelier', self::Client => '/mon-compte' };
    }
}
