<?php
namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

/** Droits par rôle : $viewRoles peuvent voir, $writeRoles peuvent créer / modifier / supprimer. */
abstract class BaseResource extends Resource
{
    protected static array $viewRoles = ['admin'];
    protected static array $writeRoles = ['admin'];

    protected static function currentRole(): ?string
    {
        return auth()->user()?->role?->value;
    }

    public static function canWrite(): bool { return in_array(static::currentRole(), static::$writeRoles, true); }
    public static function canViewAny(): bool { return in_array(static::currentRole(), static::$viewRoles, true); }
    public static function canCreate(): bool { return static::canWrite(); }
    public static function canEdit(Model $record): bool { return static::canWrite(); }
    public static function canDelete(Model $record): bool { return static::canWrite(); }
    public static function canDeleteAny(): bool { return static::canWrite(); }
}
