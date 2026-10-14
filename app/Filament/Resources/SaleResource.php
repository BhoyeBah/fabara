<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Support\Money;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends BaseResource
{
    protected static array $viewRoles = ['admin', 'gerante'];
    protected static array $writeRoles = ['admin', 'gerante'];
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Ventes et factures';
    protected static ?string $modelLabel = 'vente';
    protected static ?string $pluralModelLabel = 'ventes';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Ventes';

    // Les ventes se font uniquement à la page « Caisse » (prix du catalogue, non modifiables).
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('N°')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('total')->label('Total')->formatStateUsing(fn ($state) => Money::fmt($state)),
                Tables\Columns\TextColumn::make('paid')->label('Versé')->formatStateUsing(fn ($state) => Money::fmt($state)),
                Tables\Columns\TextColumn::make('state')->label('État')->badge()
                    ->state(fn ($record) => $record->cancelled_at ? 'Annulée' : ($record->balance > 0 ? 'Reste ' . Money::fmt($record->balance) : 'Soldée'))
                    ->color(fn ($state) => $state === 'Annulée' ? 'gray' : (str_starts_with($state, 'Reste') ? 'danger' : 'success')),
                Tables\Columns\TextColumn::make('method')->label('Paiement')->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('unpaid')->label('Reste à payer')->query(fn ($query) => $query->whereNull('cancelled_at')->whereColumn('paid', '<', 'total')),
                Tables\Filters\Filter::make('cancelled')->label('Annulées')->query(fn ($query) => $query->whereNotNull('cancelled_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('receipt')->label('Reçu')->icon('heroicon-o-receipt-percent')
                    ->url(fn ($record) => route('receipt', $record))->openUrlInNewTab(),
                Tables\Actions\Action::make('pdf')->label('Facture A4')->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('invoice', $record))->openUrlInNewTab(),
                Tables\Actions\Action::make('settle')->label('Encaisser le reste')->icon('heroicon-o-banknotes')->color('success')
                    ->visible(fn ($record) => $record->balance > 0)->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['paid' => $record->total])),
                Tables\Actions\Action::make('cancel')->label('Annuler la vente')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($record) => auth()->user()->role->value === 'admin' && ! $record->cancelled_at)
                    ->form([Forms\Components\Textarea::make('reason')->label('Motif de l’annulation')->required()])
                    ->modalDescription('Le stock des articles sera remis et la vente ne comptera plus dans les chiffres.')
                    ->action(function ($record, array $data) {
                        $record->cancel($data['reason']);
                        Notification::make()->title('Vente annulée, stock remis.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSales::route('/')];
    }
}
