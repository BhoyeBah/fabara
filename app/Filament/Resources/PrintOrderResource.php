<?php
namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\PrintOrderResource\Pages;
use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class PrintOrderResource extends BaseResource
{
    protected static array $viewRoles = ['admin', 'agent'];
    protected static array $writeRoles = ['admin', 'agent'];
    protected static ?string $model = PrintOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationLabel = 'Commandes imprimerie';
    protected static ?string $modelLabel = 'commande';
    protected static ?string $pluralModelLabel = 'commandes';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Imprimerie';

    protected static function isAdmin(): bool
    {
        return static::currentRole() === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Le client')->columns(2)->schema([
                Forms\Components\TextInput::make('customer_name')->label('Nom du client')->required(),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->tel()->helperText('Le client s’en sert avec son code pour suivre sa commande.'),
            ]),
            Forms\Components\Section::make('Le travail')->columns(2)->schema([
                Forms\Components\Select::make('product_id')->label('Article du catalogue (le prix se met tout seul)')->searchable()->live()
                    ->options(fn () => Product::where('is_active', true)->whereHas('category', fn ($c) => $c->where('menu', 'imprimerie'))->orderBy('name')->pluck('name', 'id'))
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if (! filled($get('title')) && ($p = Product::find($state))) {
                            $set('title', $p->name);
                        }
                    }),
                Forms\Components\TextInput::make('title')->label('Description du travail (ex : 200 flyers A5)')->required(),
                Forms\Components\TextInput::make('quantity')->label('Quantité')->numeric()->default(1)->minValue(1),
                Forms\Components\DatePicker::make('due_date')->label('Date de livraison prévue'),
                Forms\Components\Textarea::make('notes')->label('Notes (couleurs, format, fichiers reçus…)')->rows(2)->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Prix et suivi')->columns(2)->schema([
                Forms\Components\TextInput::make('price')->label('Prix')->numeric()->default(0)
                    ->disabled(fn () => ! static::isAdmin())
                    ->helperText('Pris dans le catalogue, ou fixé par l’administrateur (devis).'),
                Forms\Components\TextInput::make('deposit')->label('Acompte reçu')->numeric()->default(0),
                Forms\Components\TextInput::make('material_cost')->label('Coût matière (facultatif)')->numeric()->default(0),
                Forms\Components\Select::make('status')->label('Où en est la commande ?')->options(OrderStatus::class)->default(OrderStatus::Nouvelle->value)->required(),
                Forms\Components\Select::make('user_id')->label('Agent responsable')->options(fn () => User::where('role', 'agent')->pluck('name', 'id')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Code client')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Reçue le')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Travail')->searchable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone'),
                Tables\Columns\TextColumn::make('status')->label('Statut')->badge(),
                Tables\Columns\TextColumn::make('price')->label('Prix')->formatStateUsing(fn ($state) => $state > 0 ? Money::fmt($state) : 'À fixer par l’admin')
                    ->color(fn ($state) => $state > 0 ? null : 'warning'),
                Tables\Columns\TextColumn::make('deposit')->label('Acompte')->formatStateUsing(fn ($state) => Money::fmt($state)),
                Tables\Columns\TextColumn::make('due_date')->label('Pour le')->date('d/m/Y'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([Tables\Filters\SelectFilter::make('status')->label('Statut')->options(OrderStatus::class)])
            ->actions([
                Tables\Actions\Action::make('next')->icon('heroicon-o-arrow-right')
                    ->label(fn ($record) => 'Passer à « ' . $record->status->next()?->getLabel() . ' »')
                    ->visible(fn ($record) => $record->status->next() !== null)
                    ->action(fn ($record) => $record->advance()),
                Tables\Actions\Action::make('deliver')->label('Livrer et facturer')->icon('heroicon-o-banknotes')->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === OrderStatus::Prete)
                    ->action(function ($record) {
                        if ($record->price <= 0) {
                            Notification::make()->title('Le prix de cette commande doit d’abord être fixé par l’administrateur.')->warning()->send();
                            return;
                        }
                        $sale = $record->deliver();
                        Notification::make()->title('Facture ' . $sale->number . ' créée.')->success()->send();
                    }),
                Tables\Actions\Action::make('pdf')->label('Facture PDF')->icon('heroicon-o-printer')
                    ->visible(fn ($record) => $record->sale_id !== null)
                    ->url(fn ($record) => route('invoice', $record->sale_id))->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->visible(fn () => static::canWrite()),
                Tables\Actions\DeleteAction::make()->visible(fn () => auth()->user()->role->value === 'admin'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePrintOrders::route('/')];
    }
}
