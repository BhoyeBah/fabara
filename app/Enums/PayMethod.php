<?php
namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum PayMethod: string implements HasLabel
{
    case Especes = 'especes';
    case MobileMoney = 'mobile_money';
    case Virement = 'virement';
    case Carte = 'carte';

    public function getLabel(): ?string
    {
        return match ($this) { self::Especes => 'Espèces', self::MobileMoney => 'Mobile money', self::Virement => 'Virement', self::Carte => 'Carte' };
    }
}
