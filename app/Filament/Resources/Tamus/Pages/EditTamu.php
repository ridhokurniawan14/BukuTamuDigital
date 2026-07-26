<?php

namespace App\Filament\Resources\Tamus\Pages;

use App\Filament\Resources\Tamus\TamuResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTamu extends EditRecord
{
    protected static string $resource = TamuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
