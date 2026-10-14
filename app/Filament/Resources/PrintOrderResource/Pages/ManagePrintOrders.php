<?php
namespace App\Filament\Resources\PrintOrderResource\Pages;

use App\Filament\Resources\PrintOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePrintOrders extends ManageRecords
{
    protected static string $resource = PrintOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->visible(fn () => static::getResource()::canWrite())];
    }
}
