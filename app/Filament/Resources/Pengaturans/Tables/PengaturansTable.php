<?php

namespace App\Filament\Resources\Pengaturans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengaturansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_instansi')
                    ->searchable(),
                TextColumn::make('singkatan_instansi')
                    ->searchable(),
                TextColumn::make('logo_instansi')
                    ->searchable(),
                TextColumn::make('favicon')
                    ->searchable(),
                TextColumn::make('gambar_background')
                    ->searchable(),
                TextColumn::make('warna_utama')
                    ->searchable(),
                IconColumn::make('wajib_foto')
                    ->boolean(),
                IconColumn::make('wajib_ttd')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
