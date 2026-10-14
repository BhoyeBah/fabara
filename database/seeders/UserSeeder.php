<?php
namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // En ligne (production) : seul l'administrateur est créé, avec ADMIN_EMAIL / ADMIN_PASSWORD de l'hébergeur.
        if (app()->environment('production')) {
            $email = env('ADMIN_EMAIL');
            $pass = env('ADMIN_PASSWORD');
            if ($email && $pass) {
                User::firstOrCreate(['email' => $email], ['name' => 'Administrateur', 'role' => Role::Admin, 'password' => $pass, 'is_active' => true]);
            }
            return;
        }
        // CHANGEZ ces mots de passe dès la première connexion (Admin > Utilisateurs).
        $users = [
            ['Administrateur', 'admin@fabara.local', Role::Admin, 'ChangeMe-Admin-2026'],
            ['Gérante', 'gerante@fabara.local', Role::Gerante, 'ChangeMe-Gerante-2026'],
            ['Agent 1', 'agent1@fabara.local', Role::Agent, 'ChangeMe-Agent-2026'],
        ];
        foreach ($users as [$name, $email, $role, $pass]) {
            User::firstOrCreate(['email' => $email], ['name' => $name, 'role' => $role, 'password' => $pass, 'is_active' => true]);
        }
    }
}
