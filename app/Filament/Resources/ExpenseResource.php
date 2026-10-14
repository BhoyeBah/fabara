<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends BaseResource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Charges';
    protected static ?string $modelLabel = 'charge';
    protected static ?string $pluralModelLabel = 'charges';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationGroup = 'Achats et finances';

    public const CATEGORIES = ['Loyer', 'Salaires', 'Électricité et eau', 'Transport', 'Matières imprimerie', 'Achat de marchandises', 'Autre'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('spent_on')->label('Date')->default(now())->required(),
            Forms\Components\TextInput::make('label')->label('Libellé')->required(),
            Forms\Components\Select::make('category')->label('Catégorie')->options(array_combine(self::CATEGORIES, self::CATEGORIES))->required(),
            Forms\Components\TextInput::make('amount')->label('Montant')->numeric()->minValue(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('spent_on')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('label')->label('Libellé')->searchable(),
                Tables\Columns\TextColumn::make('category')->label('Catégorie')->badge(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->formatStateUsing(fn ($state) => Money::fmt($state))
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => Money::fmt($state))),
            ])
            ->defaultSort('spent_on', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageExpenses::route('/')];
    }
}
