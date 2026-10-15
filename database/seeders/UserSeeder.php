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
                $admin = User::firstOrCreate(['email' => $email], ['name' => 'Administrateur', 'role' => Role::Admin, 'password' => $pass, 'is_active' => true]);
                if (! $admin->is_active) {
                    $admin->update(['is_active' => true]);
                }
            }
            return;
        }
        // CHANGEZ ces mots de passe dès la première connexion (Admin > Utilisateurs).
        $users = [
            ['Administrateur', 'fabara@gmail.com', Role::Admin, 'Abcde150'],
            ['Gérante', 'aissatou@gmail.com', Role::Gerante, 'Abcde150'],
            ['Agent', 'biro@gmail.com', Role::Agent, 'Abcde150'],
        ];
        foreach ($users as [$name, $email, $role, $pass]) {
            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'role' => $role, 'password' => $pass, 'is_active' => true]);
            if (! $user->is_active) {
                $user->update(['is_active' => true]);
            }
        }
    }
}
