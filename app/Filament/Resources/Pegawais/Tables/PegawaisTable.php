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
use Filament\Forms\Get;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Pegawai')
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->label('No. HP / WA')
                    ->searchable()
                    // Menambahkan icon chat berwarna hijau
                    ->icon('heroicon-m-chat-bubble-left-ellipsis')
                    ->iconColor('success')
                    // Membuatnya menjadi link yang bisa diklik
                    ->url(function ($record) {
                        $nomor = $record->no_hp;

                        // Hapus karakter selain angka (opsional untuk jaga-jaga)
                        $nomor = preg_replace('/[^0-9]/', '', $nomor);

                        // Pengecekan format nomor agar selalu berawalan 62
                        if (str_starts_with($nomor, '0')) {
                            // Jika berawalan 0, ubah jadi 62
                            $nomor = '62' . substr($nomor, 1);
                        } elseif (str_starts_with($nomor, '8')) {
                            // Jika langsung 8 (seperti datamu: 8968215449), tambahkan 62 di depannya
                            $nomor = '62' . $nomor;
                        }

                        // Kembalikan URL WhatsApp
                        return "https://wa.me/{$nomor}";
                    })
                    // Buka di tab baru agar user tidak keluar dari halaman dashboard
                    ->openUrlInNewTab(),

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
