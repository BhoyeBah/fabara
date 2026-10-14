<?php
namespace App\Enums;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor
{
    case Nouvelle = 'nouvelle';
    case EnCours = 'en_cours';
    case Prete = 'prete';
    case Livree = 'livree';

    public function getLabel(): ?string
    {
        return match ($this) { self::Nouvelle => 'Nouvelle', self::EnCours => 'En cours', self::Prete => 'Prête', self::Livree => 'Livrée' };
    }

    public function getColor(): string|array|null
    {
        return match ($this) { self::Nouvelle => 'gray', self::EnCours => 'warning', self::Prete => 'info', self::Livree => 'success' };
    }

    public function next(): ?self
    {
        return match ($this) { self::Nouvelle => self::EnCours, self::EnCours => self::Prete, default => null };
    }
}
