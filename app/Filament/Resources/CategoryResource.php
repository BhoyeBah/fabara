<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends BaseResource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Menus et sous-menus';
    protected static ?string $modelLabel = 'sous-menu';
    protected static ?string $pluralModelLabel = 'sous-menus';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationGroup = 'Catalogue et stock';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('menu')->label('Menu')->options(['boutique' => 'Boutique', 'imprimerie' => 'Imprimerie'])->required(),
            Forms\Components\TextInput::make('name')->label('Nom du sous-menu')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Get $get, Set $set) => $get('slug') ?: $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->label('Adresse (automatique)')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('position')->label('Ordre')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('menu')->label('Menu')->badge()->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('name')->label('Sous-menu')->searchable(),
                Tables\Columns\TextColumn::make('products_count')->label('Articles')->counts('products'),
                Tables\Columns\TextColumn::make('position')->label('Ordre')->sortable(),
            ])
            ->defaultSort('position')
            ->filters([Tables\Filters\SelectFilter::make('menu')->options(['boutique' => 'Boutique', 'imprimerie' => 'Imprimerie'])])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn () => static::canWrite()),
                Tables\Actions\DeleteAction::make()->visible(fn () => static::canWrite()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageCategories::route('/')];
    }
}
