<?php

namespace App\Filament\Resources\Pengaturans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PengaturanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_instansi')
                    ->required()
                    ->default('Buku Tamu Digital'),
                TextInput::make('singkatan_instansi')
                    ->required()
                    ->default('BTD'),
                TextInput::make('logo_instansi'),
                TextInput::make('favicon'),
                TextInput::make('gambar_background'),
                TextInput::make('warna_utama')
                    ->required()
                    ->default('#f59e0b'),
                Textarea::make('alamat_instansi')
                    ->columnSpanFull(),
                Textarea::make('pesan_sambutan')
                    ->columnSpanFull(),
                Toggle::make('wajib_foto')
                    ->required(),
                Toggle::make('wajib_ttd')
                    ->required(),
            ]);
    }
}
