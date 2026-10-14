<?php
namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Importe des articles depuis un fichier Excel (.xlsx) ou CSV.
 * Colonnes : sous_menu ; nom ; prix ; stock ; cout ; description ; image
 *  - sous_menu : nom d'un sous-menu existant (ex : « Sacs », « Cartes de visite »)
 *  - prix vide = « Prix sur devis » ; stock vide = illimité (services)
 *  - image : nom d'un fichier placé dans le dossier « images » à côté du CSV (facultatif)
 */
class CatalogImporter
{
    public int $created = 0;
    public int $updated = 0;
    /** @var string[] */
    public array $errors = [];

    public function __construct(private bool $demo = false) {}

    public function run(string $csvPath, ?string $imagesDir = null): self
    {
        if (! is_file($csvPath)) {
            $this->errors[] = "Fichier introuvable : $csvPath";
            return $this;
        }
        $imagesDir ??= dirname($csvPath) . '/images';

        $categories = [];
        foreach (Category::all() as $c) {
            $categories[Str::slug($c->name)] = $c;
            $categories[$c->slug] = $c;
        }

        $all = TableReader::read($csvPath);
        $header = array_map(fn ($x) => Str::slug(trim((string) $x), '_'), array_shift($all) ?? []);

        $line = 1;
        foreach ($all as $raw) {
            $line++;
            if (! array_filter($raw, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }
            $row = array_combine($header, array_pad(array_slice($raw, 0, count($header)), count($header), ''));
            $name = trim($row['nom'] ?? '');
            $sub = trim($row['sous_menu'] ?? '');
            if ($name === '' || $sub === '') {
                $this->errors[] = "Ligne $line : « nom » et « sous_menu » sont obligatoires.";
                continue;
            }
            $cat = $categories[Str::slug($sub)] ?? null;
            if (! $cat) {
                $this->errors[] = "Ligne $line : le sous-menu « $sub » n'existe pas (créez-le dans Admin > Menus et sous-menus).";
                continue;
            }

            $data = [
                'price' => $this->num($row['prix'] ?? ''),
                'stock' => ($s = $this->num($row['stock'] ?? '')) === null ? null : (int) $s,
                'cost_price' => $this->num($row['cout'] ?? '') ?? 0,
                'description' => trim($row['description'] ?? '') ?: null,
                'is_active' => true,
            ];
            $img = trim($row['image'] ?? '');
            if ($img !== '' && is_file("$imagesDir/$img")) {
                $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION)) ?: 'jpg';
                $data['image'] = Storage::disk('public')->putFileAs('products', new File("$imagesDir/$img"), Str::slug($name) . '.' . $ext);
            } elseif ($img !== '') {
                $this->errors[] = "Ligne $line : image « $img » introuvable dans $imagesDir (l'article est importé sans photo).";
            }

            $product = Product::where('category_id', $cat->id)->where('name', $name)->first();
            if ($product) {
                $product->update($data);
                $this->updated++;
            } else {
                Product::create($data + ['category_id' => $cat->id, 'name' => $name, 'is_demo' => $this->demo]);
                $this->created++;
            }
        }
        return $this;
    }

    private function num(mixed $v): ?float
    {
        $v = str_replace([' ', "\u{00A0}"], '', trim((string) $v));
        if ($v === '') {
            return null;
        }
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float) $v : null;
    }
}
