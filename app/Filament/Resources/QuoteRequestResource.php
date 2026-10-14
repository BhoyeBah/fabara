<?php
namespace App\Filament\Resources;

use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Models\QuoteRequest;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends BaseResource
{
    protected static array $viewRoles = ['admin', 'gerante', 'agent'];
    protected static array $writeRoles = [];
    protected static ?string $model = QuoteRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Demandes clients';
    protected static ?string $modelLabel = 'demande';
    protected static ?string $pluralModelLabel = 'demandes clients';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Imprimerie';

    public static function table(Table $table): Table
    {
        $labels = ['nouvelle' => 'Nouvelle', 'vue' => 'Vue', 'traitee' => 'Traitée'];
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Reçue le')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Demande')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('details')->label('Détails')->wrap()->limit(80),
                Tables\Columns\TextColumn::make('status')->label('État')->badge()
                    ->formatStateUsing(fn ($state) => $labels[$state] ?? $state)
                    ->color(fn ($state) => ['nouvelle' => 'danger', 'vue' => 'warning', 'traitee' => 'success'][$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([Tables\Filters\SelectFilter::make('status')->label('État')->options($labels)])
            ->actions([
                Tables\Actions\Action::make('whatsapp')->label('WhatsApp')->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn ($record) => 'https://wa.me/' . preg_replace('/\D/', '', $record->phone))->openUrlInNewTab(),
                Tables\Actions\Action::make('convert')->label('Créer la commande')->icon('heroicon-o-printer')->color('primary')
                    ->visible(fn ($record) => in_array(auth()->user()->role->value, ['admin', 'agent'], true) && ! $record->print_order_id)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $o = $record->toPrintOrder();
                        Notification::make()->title('Commande créée (code de suivi ' . $o->code . ').')->success()->send();
                    }),
                Tables\Actions\Action::make('done')->label('Marquer traitée')->icon('heroicon-o-check')
                    ->visible(fn ($record) => $record->status !== 'traitee')
                    ->action(fn ($record) => $record->update(['status' => 'traitee'])),
                Tables\Actions\Action::make('seen')->label('Marquer vue')->icon('heroicon-o-eye')
                    ->visible(fn ($record) => $record->status === 'nouvelle')
                    ->action(fn ($record) => $record->update(['status' => 'vue'])),
                Tables\Actions\DeleteAction::make()->visible(fn () => auth()->user()->role->value === 'admin'),
            ]);
    }

    public static function canDelete($record): bool { return auth()->user()?->role?->value === 'admin'; }
    public static function canDeleteAny(): bool { return auth()->user()?->role?->value === 'admin'; }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageQuoteRequests::route('/')];
    }
}
