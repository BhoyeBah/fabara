<?php
namespace Database\Seeders;

use App\Support\CatalogImporter;
use App\Support\Setting;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Catalogue d'exemple réaliste (65 articles, photos d'exemple) pour voir le site « plein ».
     * Les prix sont indicatifs (en GNF) : modifiez-les dans Admin > Articles.
     * Pour tout supprimer : php artisan fabara:clear-demo
     */
    public function run(): void
    {
        if (! Setting::get('currency')) {
            Setting::put('currency', 'GNF');
        }
        $imp = (new CatalogImporter(demo: true))->run(__DIR__ . '/catalog/articles.csv');
        foreach ($imp->errors as $e) {
            $this->command?->warn($e);
        }
        $this->command?->info("Catalogue d'exemple : {$imp->created} créés, {$imp->updated} mis à jour.");
    }
}
