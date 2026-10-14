<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends BaseResource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Fournisseurs';
    protected static ?string $modelLabel = 'fournisseur';
    protected static ?string $pluralModelLabel = 'fournisseurs';
    protected static ?int $navigationSort = 41;
    protected static ?string $navigationGroup = 'Achats et finances';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nom')->required(),
            Forms\Components\TextInput::make('phone')->label('Téléphone')->tel(),
            Forms\Components\Textarea::make('notes')->label('Notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withSum('purchases as bought', 'amount')->withSum('purchases as paid_sum', 'paid'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone'),
                Tables\Columns\TextColumn::make('bought')->label('Total achats')->formatStateUsing(fn ($state) => Money::fmt($state ?? 0)),
                Tables\Columns\TextColumn::make('debt')->label('Reste à payer')->badge()
                    ->state(fn ($record) => max(0, (float) ($record->bought ?? 0) - (float) ($record->paid_sum ?? 0)))
                    ->formatStateUsing(fn ($state) => $state > 0 ? Money::fmt($state) : '—')->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageSuppliers::route('/')];
    }
}
