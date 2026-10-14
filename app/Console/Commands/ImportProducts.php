<?php
namespace App\Console\Commands;

use App\Support\CatalogImporter;
use Illuminate\Console\Command;

class ImportProducts extends Command
{
    protected $signature = 'fabara:import-products {file? : fichier CSV (défaut : storage/app/import/articles.csv)} {--demo : marquer comme articles d\'exemple}';
    protected $description = 'Importe des articles depuis un fichier CSV (photos dans le dossier "images" à côté du CSV)';

    public function handle(): int
    {
        $file = $this->argument('file') ?: storage_path('app/import/articles.csv');
        $imp = (new CatalogImporter((bool) $this->option('demo')))->run($file);
        foreach ($imp->errors as $e) {
            $this->warn($e);
        }
        $this->info("Articles créés : {$imp->created} · mis à jour : {$imp->updated}");
        return $imp->errors && ! ($imp->created + $imp->updated) ? self::FAILURE : self::SUCCESS;
    }
}
