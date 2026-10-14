<?php
namespace App\Filament\Pages;

use App\Support\Report;
use Filament\Pages\Page;

class Accounting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Comptabilité';
    protected static ?string $title = 'Comptabilité';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Achats et finances';
    protected static string $view = 'filament.pages.accounting';

    public string $month = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->role?->value === 'admin';
    }

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function getReport(): array
    {
        return Report::build($this->month ?: null);
    }
}
