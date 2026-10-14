<?php
namespace App\Providers;

use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class FabaraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Les photos envoyées sont servies par la route /media (voir routes/web.php) :
        // plus besoin de « storage:link », et l'adresse reste correcte quel que soit APP_URL ou le port.
        config(['filesystems.disks.public.url' => '/media']);
    }

    public function boot(): void
    {
        // Hébergé derrière un proxy HTTPS (Railway, Render…) : toutes les adresses générées en https.
        if (env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }

        // Réglages communs à tous les tableaux des interfaces équipe.
        Table::configureUsing(fn (Table $table) => $table
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25));
    }
}
