<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends BaseResource
{
    protected static array $viewRoles = ['admin', 'gerante', 'agent'];
    protected static array $writeRoles = ['admin', 'gerante', 'agent'];
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clients';
    protected static ?string $modelLabel = 'client';
    protected static ?string $pluralModelLabel = 'clients';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationGroup = 'Ventes';

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
            ->modifyQueryUsing(fn ($query) => $query
                ->withSum(['sales as sales_total' => fn ($s) => $s->whereNull('cancelled_at')], 'total')
                ->withSum(['sales as sales_paid' => fn ($s) => $s->whereNull('cancelled_at')], 'paid'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('sales_total')->label('Total achats')->formatStateUsing(fn ($state) => Money::fmt($state ?? 0)),
                Tables\Columns\TextColumn::make('due')->label('Reste dû')->badge()
                    ->state(fn ($record) => max(0, (float) ($record->sales_total ?? 0) - (float) ($record->sales_paid ?? 0)))
                    ->formatStateUsing(fn ($state) => $state > 0 ? Money::fmt($state) : '—')->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn () => static::canWrite()),
                Tables\Actions\DeleteAction::make()->visible(fn () => auth()->user()->role->value === 'admin'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCustomers::route('/')];
    }
}
