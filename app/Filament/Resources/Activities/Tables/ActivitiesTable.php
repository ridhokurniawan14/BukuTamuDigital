<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->default('Sistem / Guest')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Aktivitas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('subject_type')
                    ->label('Tabel / Model')
                    ->formatStateUsing(fn($state) => class_basename($state)) // Membuang tulisan panjang "App/Models/"
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc') // Otomatis yang paling baru ada di atas
            ->filters([
                //
            ])
            ->actions([
                // Kosongkan agar tidak ada tombol Edit/Hapus di setiap baris
            ])
            ->bulkActions([
                // Kosongkan agar tidak ada fitur Hapus Massal
            ])
            ->headerActions([
                Action::make('clear_logs')
                    ->label('Bersihkan Log')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation() // Memunculkan pop-up konfirmasi
                    ->modalHeading('Bersihkan Log Aktivitas Lama')
                    ->modalDescription('Tindakan ini akan menghapus semua log aktivitas dan hanya menyisakan 10 log terbaru. Apakah Anda yakin?')
                    ->action(function () {
                        // 1. Ambil 10 ID aktivitas terbaru dari database
                        $latestIds = Activity::latest('id')->take(10)->pluck('id');

                        // 2. Hapus semua aktivitas yang ID-nya TIDAK TERMASUK dalam 10 ID terbaru tersebut
                        Activity::whereNotIn('id', $latestIds)->delete();

                        // 3. Munculkan notifikasi sukses hijau di pojok kanan atas
                        Notification::make()
                            ->title('Log Berhasil Dibersihkan!')
                            ->body('Semua log lama telah dihapus, menyisakan 10 aktivitas terbaru.')
                            ->success()
                            ->send();
                    })
            ]);
    }
}
