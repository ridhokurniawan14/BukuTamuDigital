<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('jabatan')
                    ->default(null),
                TextInput::make('no_hp')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
