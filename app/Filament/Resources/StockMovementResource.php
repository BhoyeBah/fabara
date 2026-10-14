<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementResource extends BaseResource
{
    protected static array $viewRoles = ['admin'];
    protected static array $writeRoles = [];
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationLabel = 'Historique du stock';
    protected static ?string $modelLabel = 'mouvement';
    protected static ?string $pluralModelLabel = 'mouvements de stock';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationGroup = 'Catalogue et stock';

    public static function table(Table $table): Table
    {
        $types = ['vente' => 'Vente', 'achat' => 'Achat', 'ajustement' => 'Ajustement', 'retour' => 'Retour / annulation'];
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('product.name')->label('Article')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()->formatStateUsing(fn ($state) => $types[$state] ?? $state),
                Tables\Columns\TextColumn::make('quantity')->label('Quantité')->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . $state)
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('note')->label('Détail')->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('Par'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([Tables\Filters\SelectFilter::make('type')->options($types)]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageStockMovements::route('/')];
    }
}
