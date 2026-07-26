<?php

namespace App\Filament\Resources\Tamus\Pages;

use App\Filament\Resources\Tamus\TamuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTamus extends ListRecords
{
    protected static string $resource = TamuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
