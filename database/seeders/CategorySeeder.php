<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'boutique' => ['Vêtements pour Femme', 'Vêtements pour Homme', 'Tunique MakiSall', 'Produits Cosmétiques de Thaïlande', 'Chaussures', 'Bijoux et Montres', 'Accessoires IPhones et Macs', 'Sacs'],
            'imprimerie' => ['Cachets', 'Carnets de Factures, Reçus, Bons…', 'Cartes de visite', 'Badges numériques', 'Tableaux Photos', 'Sacs Personnalisés', 'Éventails personnalisés', 'Étiquettes personnalisées', 'Bâches', 'Autocollant', 'Calendrier', 'Offset', 'T-shirt et Polo', 'Porte-clés personnalisés', 'Pochettes personnalisées', 'Uv', 'Blocs Notes - Stylos Personnalisés'],
        ];
        foreach ($menus as $menu => $names) {
            foreach ($names as $i => $name) {
                Category::updateOrCreate(['slug' => Str::slug($name)], ['menu' => $menu, 'name' => $name, 'position' => $i + 1]);
            }
        }
    }
}
