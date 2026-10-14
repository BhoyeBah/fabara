<?php
namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseResource extends BaseResource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Achats fournisseurs';
    protected static ?string $modelLabel = 'achat';
    protected static ?string $pluralModelLabel = 'achats';
    protected static ?int $navigationSort = 42;
    protected static ?string $navigationGroup = 'Achats et finances';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('supplier_id')->label('Fournisseur')->relationship('supplier', 'name')->searchable()->preload()
                ->createOptionForm([Forms\Components\TextInput::make('name')->label('Nom')->required(), Forms\Components\TextInput::make('phone')->label('Téléphone')]),
            Forms\Components\DatePicker::make('purchased_on')->label('Date')->default(now())->required(),
            Forms\Components\TextInput::make('label')->label('Libellé (ex : 20 robes)')->required(),
            Forms\Components\TextInput::make('amount')->label('Montant total')->numeric()->minValue(0)->required(),
            Forms\Components\TextInput::make('paid')->label('Déjà payé')->numeric()->minValue(0)->default(0),
            Forms\Components\DatePicker::make('due_date')->label('À payer avant le'),
            Forms\Components\Select::make('product_id')->label('Article reçu (facultatif)')->searchable()
                ->options(fn () => Product::whereNotNull('stock')->orderBy('name')->pluck('name', 'id'))
                ->helperText('Avec la quantité, le stock augmente automatiquement.'),
            Forms\Components\TextInput::make('quantity')->label('Quantité reçue')->numeric()->integer()->minValue(1),
            Forms\Components\Textarea::make('notes')->label('Notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchased_on')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Fournisseur'),
                Tables\Columns\TextColumn::make('label')->label('Libellé')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->formatStateUsing(fn ($state) => Money::fmt($state))
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => Money::fmt($state))),
                Tables\Columns\TextColumn::make('state')->label('État')->badge()
                    ->state(fn ($record) => $record->balance > 0 ? 'Reste ' . Money::fmt($record->balance) : 'Payé')
                    ->color(fn ($state) => $state === 'Payé' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('due_date')->label('Échéance')->date('d/m/Y'),
            ])
            ->defaultSort('purchased_on', 'desc')
            ->filters([Tables\Filters\Filter::make('unpaid')->label('À payer')->query(fn ($query) => $query->whereColumn('paid', '<', 'amount'))])
            ->actions([
                Tables\Actions\Action::make('pay')->label('Payer le reste')->icon('heroicon-o-banknotes')->color('success')
                    ->visible(fn ($record) => $record->balance > 0)->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['paid' => $record->amount])),
                Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePurchases::route('/')];
    }
}
