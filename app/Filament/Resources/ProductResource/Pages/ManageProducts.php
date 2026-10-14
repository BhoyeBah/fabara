<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        $can = fn () => static::getResource()::canWrite();

        return [
            Actions\Action::make('bulk')->label('Ajout rapide (plusieurs articles)')->icon('heroicon-o-squares-plus')->color('gray')
                ->visible($can)->modalWidth('7xl')->modalSubmitActionLabel('Enregistrer les articles')
                ->form([
                    Forms\Components\Repeater::make('rows')->label('Articles à ajouter')->addActionLabel('Ajouter une ligne')
                        ->defaultItems(4)->columns(3)->schema([
                            Forms\Components\Select::make('category_id')->label('Sous-menu')->searchable()->required()->live()
                                ->options(fn () => ProductResource::categoryOptions()),
                            Forms\Components\TextInput::make('name')->label('Nom')->required(),
                            Forms\Components\TextInput::make('price')->label('Prix de vente')->numeric()->minValue(0)
                                ->required(fn (Get $get) => ProductResource::requiresPrice($get('category_id'))),
                            Forms\Components\TextInput::make('stock')->label('Stock (vide = illimité)')->numeric()->integer()->minValue(0),
                            Forms\Components\TextInput::make('cost_price')->label('Prix d’achat (facultatif)')->numeric()->minValue(0),
                            Forms\Components\FileUpload::make('image')->label('Photo')->image()->disk('public')->directory('products')
                                ->maxSize(8192)->imageResizeMode('contain')->imageResizeTargetWidth('900')->imageResizeTargetHeight('900'),
                        ]),
                ])
                ->action(function (array $data) {
                    $n = 0;
                    foreach ($data['rows'] ?? [] as $r) {
                        Product::create([
                            'category_id' => $r['category_id'], 'name' => $r['name'],
                            'price' => ($r['price'] ?? '') === '' ? null : $r['price'],
                            'stock' => ($r['stock'] ?? '') === '' ? null : (int) $r['stock'],
                            'cost_price' => $r['cost_price'] ?: 0, 'image' => $r['image'] ?? null, 'is_active' => true,
                        ]);
                        $n++;
                    }
                    Notification::make()->title($n . ' article(s) ajouté(s).')->success()->send();
                }),
            Actions\Action::make('import')->label('Importer un fichier')->icon('heroicon-o-arrow-up-tray')->color('gray')
                ->visible($can)->modalSubmitActionLabel('Importer')->modalWidth('xl')
                ->modalDescription('Remplissez le modèle dans Excel puis ajoutez le fichier (.xlsx ou .csv) ici. Les photos sont facultatives : mettez-les dans un seul fichier ZIP et écrivez leur nom dans la colonne « image ». Un article déjà existant (même nom, même sous-menu) est mis à jour.')
                ->form([
                    Forms\Components\Placeholder::make('tpl')->label('')->content(new \Illuminate\Support\HtmlString('<a href="' . route('products.template') . '" class="text-primary-600 underline">⬇ Télécharger le modèle de fichier</a>')),
                    Forms\Components\FileUpload::make('csv')->label('Fichier des articles (Excel .xlsx ou .csv)')->required()->disk('local')->directory('imports')->maxSize(10240),
                    Forms\Components\FileUpload::make('zip')->label('Photos (un fichier .zip, facultatif)')->disk('local')->directory('imports')->maxSize(102400),
                ])
                ->action(function (array $data) {
                    $disk = \Illuminate\Support\Facades\Storage::disk('local');
                    $csv = $disk->path($data['csv']);
                    $tmp = storage_path('app/imports/run-' . uniqid());
                    @mkdir($tmp . '/images', 0775, true);
                    if (! empty($data['zip'])) {
                        $z = new \ZipArchive();
                        if ($z->open($disk->path($data['zip'])) === true) {
                            for ($i = 0; $i < $z->numFiles; $i++) {
                                $n = $z->getNameIndex($i);
                                $base = basename($n);
                                if (str_ends_with($n, '/') || str_starts_with($base, '.') || str_contains($n, '__MACOSX') || ! preg_match('/\.(jpe?g|png|webp|gif)$/i', $base)) {
                                    continue;
                                }
                                file_put_contents($tmp . '/images/' . $base, $z->getFromIndex($i));
                            }
                            $z->close();
                        }
                    }
                    $imp = (new \App\Support\CatalogImporter())->run($csv, $tmp . '/images');
                    \Illuminate\Support\Facades\File::deleteDirectory($tmp);
                    $disk->delete(array_filter([$data['csv'], $data['zip'] ?? null]));
                    $n = Notification::make()->title($imp->created . ' article(s) ajouté(s), ' . $imp->updated . ' mis à jour.')
                        ->body($imp->errors ? implode("\n", array_slice($imp->errors, 0, 8)) . (count($imp->errors) > 8 ? "\n… et " . (count($imp->errors) - 8) . ' autre(s).' : '') : null);
                    ($imp->errors ? $n->warning() : $n->success())->persistent()->send();
                }),
            Actions\CreateAction::make()->visible($can),
        ];
    }
}
