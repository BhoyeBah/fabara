<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends BaseResource
{
    protected static array $viewRoles = ['admin', 'gerante'];
    protected static array $writeRoles = ['admin'];
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Articles';
    protected static ?string $modelLabel = 'article';
    protected static ?string $pluralModelLabel = 'articles';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationGroup = 'Catalogue et stock';

    public static function categoryOptions(): array
    {
        return Category::orderBy('menu')->orderBy('position')->get()
            ->groupBy(fn ($c) => ucfirst($c->menu))->map(fn ($g) => $g->pluck('name', 'id')->all())->all();
    }

    public static function requiresPrice($categoryId): bool
    {
        return $categoryId && Category::where('id', $categoryId)->value('menu') === 'boutique';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('L’article')->description('Son nom et le sous-menu où il apparaît sur le site.')->columns(2)->schema([
                Forms\Components\Select::make('category_id')->label('Sous-menu')->searchable()->required()->live()
                    ->options(fn () => static::categoryOptions()),
                Forms\Components\TextInput::make('name')->label('Nom')->required(),
                Forms\Components\Textarea::make('description')->label('Description (facultatif)')->rows(3)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Prix et stock')->description('Seul l’administrateur fixe les prix et gère le stock.')->columns(2)->schema([
                Forms\Components\TextInput::make('price')->label('Prix de vente')->numeric()->minValue(0)
                    ->required(fn (Get $get) => static::requiresPrice($get('category_id')))
                    ->helperText('Obligatoire en boutique. En imprimerie, vide = « Prix sur devis ».'),
                Forms\Components\TextInput::make('cost_price')->label('Prix d’achat (ce que l’article vous coûte)')->numeric()->default(0)
                    ->helperText('Sert à calculer votre bénéfice.'),
                Forms\Components\TextInput::make('stock')->label('Quantité en stock (vide = illimité)')->numeric()->integer()
                    ->disabled(fn (string $operation) => $operation === 'edit')->dehydrated(fn (string $operation) => $operation === 'create')
                    ->helperText('Ensuite, utilisez « Ajuster le stock » pour garder l’historique.'),
                Forms\Components\TextInput::make('min_stock')->label('Prévenir quand il en reste')->numeric()->integer()->default(3),
            ]),
            Forms\Components\Section::make('Photo et visibilité')->columns(2)->schema([
                Forms\Components\FileUpload::make('image')->label('Photo de l’article')->image()->disk('public')->directory('products')
                    ->maxSize(8192)->imageResizeMode('contain')->imageResizeTargetWidth('900')->imageResizeTargetHeight('900'),
                Forms\Components\Toggle::make('is_active')->label('Visible sur le site et à la caisse')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Photo')->disk('public')->square(),
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Sous-menu')->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Prix')->formatStateUsing(fn ($state) => $state ? Money::fmt($state) : 'Sur devis'),
                Tables\Columns\TextColumn::make('stock')->label('Stock')->placeholder('∞')
                    ->color(fn ($record) => $record->isLow() ? 'danger' : null)->weight(fn ($record) => $record->isLow() ? 'bold' : null),
                Tables\Columns\ToggleColumn::make('is_active')->label('Visible')->disabled(fn () => ! static::canWrite()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('menu')->label('Menu')->options(['boutique' => 'Boutique', 'imprimerie' => 'Imprimerie'])
                    ->query(fn (Builder $query, array $data) => $data['value'] ? $query->whereHas('category', fn ($c) => $c->where('menu', $data['value'])) : $query),
                Tables\Filters\SelectFilter::make('category_id')->label('Sous-menu')->relationship('category', 'name'),
                Tables\Filters\Filter::make('low')->label('Stock bas')
                    ->query(fn (Builder $query) => $query->whereNotNull('stock')->whereColumn('stock', '<=', 'min_stock')),
            ])
            ->actions([
                Tables\Actions\Action::make('adjust')->label('Ajuster le stock')->icon('heroicon-o-adjustments-horizontal')
                    ->visible(fn ($record) => static::canWrite() && $record->stock !== null)
                    ->form([
                        Forms\Components\TextInput::make('qty')->label('Quantité (+ entrée, − sortie)')->numeric()->integer()->required(),
                        Forms\Components\TextInput::make('note')->label('Motif (réception, casse, inventaire…)')->required(),
                    ])
                    ->action(function ($record, array $data) {
                        StockMovement::move($record, (int) $data['qty'], 'ajustement', $data['note']);
                        Notification::make()->title('Stock mis à jour.')->success()->send();
                    }),
                Tables\Actions\EditAction::make()->visible(fn () => static::canWrite()),
                Tables\Actions\DeleteAction::make()->visible(fn () => static::canWrite()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProducts::route('/')];
    }
}
