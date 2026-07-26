<?php

namespace App\Filament\Resources\Pegawais\Tables;

use App\Filament\Imports\PegawaiImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Pegawai')
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Status Aktif')
                    ->onColor('success') // Warna hijau saat aktif
                    ->offColor('danger'), // Warna merah saat tidak aktif

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
            ->headerActions([
                ImportAction::make()
                    ->importer(PegawaiImporter::class)
                    ->label('Import Pegawai')
                    ->icon('heroicon-o-arrow-down-tray') // Tambahan Icon Import
                    ->color('success'),

                CreateAction::make()
                    ->label('Tambah Pegawai')
                    ->icon('heroicon-o-plus-circle') // Tambahan Icon Plus
                    ->color('primary'),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel() // Menyembunyikan teks "Edit"
                    ->tooltip('Edit Data') // Memunculkan teks saat kursor diarahkan ke icon
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning'), // Opsional: kasih warna kuning/oren

                DeleteAction::make()
                    ->hiddenLabel() // Menyembunyikan teks "Delete"
                    ->tooltip('Hapus Data')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation(), // Minta konfirmasi sebelum hapus biar aman
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
