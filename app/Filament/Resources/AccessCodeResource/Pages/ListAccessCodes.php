<?php

namespace App\Filament\Resources\AccessCodeResource\Pages;

use App\Filament\Resources\AccessCodeResource;
use Filament\Resources\Pages\ListRecords;

class ListAccessCodes extends ListRecords
{
    protected static string $resource = AccessCodeResource::class;

    protected function getHeaderActions(): array
    {
        // Codes are generated via the table's "Generate codes" action.
        return [];
    }
}
