<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User; // Wajib import Model User untuk proteksi hapus
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable(),

                // Tambahan ekstra: Tampilkan Badge Role di tabel biar makin PRO
                TextColumn::make('roles.name')
                    ->label('Hak Akses (Role)')
                    ->badge()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
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
                // Tombol New User dengan Icon di pojok kanan atas tabel
                CreateAction::make()
                    ->label('Tambah Pengguna')
                    ->icon('heroicon-o-user-plus') // Icon khas nambah user
                    ->color('primary'),
            ])
            ->recordActions([
                // Tombol Edit (Icon Only)
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Edit Data')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning'),

                // Tombol Hapus (Icon Only) dengan Proteksi Super Admin
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Hapus Data')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    // Keajaiban terjadi di sini: Jika user punya role 'super_admin', tombol hapus akan hilang!
                    ->hidden(fn(User $record): bool => $record->hasRole('super_admin')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($action, $records) {
                            foreach ($records as $record) {
                                // Jika di dalam data yang dicentang ada Super Admin
                                if ($record->hasRole('super_admin')) {
                                    // Munculkan notifikasi error
                                    \Filament\Notifications\Notification::make()
                                        ->danger()
                                        ->title('Akses Ditolak!')
                                        ->body('Terdapat data Super Admin dalam pilihan. Super Admin tidak boleh dihapus!')
                                        ->send();

                                    // Batalkan seluruh proses hapus massal
                                    $action->halt();
                                }
                            }
                        }),
                ]),
            ]);
    }
}
