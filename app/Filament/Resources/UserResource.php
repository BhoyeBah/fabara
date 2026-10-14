<?php
namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Équipe et accès';
    protected static ?string $modelLabel = 'compte';
    protected static ?string $pluralModelLabel = 'comptes';
    protected static ?int $navigationSort = 50;
    protected static ?string $navigationGroup = 'Administration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nom')->required(),
            Forms\Components\TextInput::make('email')->label('Email (identifiant)')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone')->label('Téléphone')->tel(),
            Forms\Components\Select::make('role')->label('Rôle (interface)')->options(collect(Role::cases())->reject(fn ($r) => $r === Role::Client)->mapWithKeys(fn ($r) => [$r->value => $r->getLabel()])->all())->required(),
            Forms\Components\TextInput::make('password')->label('Mot de passe')->password()->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('À la modification : laissez vide pour ne pas changer.'),
            Forms\Components\Toggle::make('is_active')->label('Compte actif')->default(true),
        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Les comptes clients sont gérés par les clients eux-mêmes (données personnelles).
        return parent::getEloquentQuery()->where('role', '!=', 'client');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Rôle')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn ($record) => $record->id === auth()->id()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUsers::route('/')];
    }
}
