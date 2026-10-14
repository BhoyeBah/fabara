<?php
namespace App\Filament\Resources\QuoteRequestResource\Pages;

use App\Filament\Resources\QuoteRequestResource;
use Filament\Resources\Pages\ManageRecords;

class ManageQuoteRequests extends ManageRecords
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
