<?php
namespace App\Models;

use App\Enums\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'phone', 'terms_accepted_at'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'role' => Role::class, 'is_active' => 'boolean', 'email_verified_at' => 'datetime', 'terms_accepted_at' => 'datetime'];
    }

    public function isStaff(): bool
    {
        return $this->role !== Role::Client;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }
        return match ($panel->getId()) {
            'admin' => $this->role === Role::Admin,
            'caisse' => in_array($this->role, [Role::Admin, Role::Gerante], true),
            'atelier' => in_array($this->role, [Role::Admin, Role::Agent], true),
            default => false,
        };
    }
}
