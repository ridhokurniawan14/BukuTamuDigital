<?php

namespace App\Filament\Resources\Tamus\Tables;

use App\Models\Tamu;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction as ActionsCreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TamusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s') // Auto-refresh tiap 10 detik biar data tamu selalu realtime
            ->columns([
                // 1. TAMBAHAN PRO: Menampilkan Foto Tamu (Membentuk Lingkaran)
                ImageColumn::make('foto_selfie')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')), // Opsional jika foto kosong

                TextColumn::make('created_at')
                    ->label('Waktu Datang')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('nama')
                    ->searchable()
                    ->weight('bold'),

                // 2. TAMBAHAN PRO: Memunculkan Kategori dengan Badge
                TextColumn::make('kategori_keperluan')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Bisa disembunyikan jika layar penuh

                TextColumn::make('asal_instansi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('pegawai.nama')
                    ->label('Menemui')
                    ->searchable(),

                ToggleColumn::make('is_lsm')
                    ->label('LSM?'),

                // 3. TAMBAHAN PRO: Warna Dinamis untuk Jam Keluar
                TextColumn::make('waktu_keluar')
                    ->label('Jam Keluar')
                    ->dateTime('H:i')
                    ->placeholder('Belum Pulang')
                    ->badge()
                    // Jika state (waktu) kosong, warna merah. Jika terisi, warna hijau.
                    ->color(fn($state) => $state === null ? 'danger' : 'success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_lsm')
                    ->label('Status LSM'),
                TrashedFilter::make(),
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal'),
                        DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                // Pindah Tombol New Tamu ke dalam Tabel
                ActionsCreateAction::make()
                    ->label('Tambah Tamu')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary'),
            ])
            ->recordActions([
                // Tombol WA (Icon Only)
                Action::make('hubungi_wa')
                    ->hiddenLabel()
                    ->tooltip('WA Pegawai') // Tooltip penting agar admin tahu fungsi icon-nya
                    ->icon('heroicon-m-chat-bubble-left-ellipsis') // Menggunakan -m- agar ukuran proporsional
                    ->color('success')
                    ->url(fn(Tamu $record) => "https://wa.me/" . ($record->pegawai->no_hp ?? '') . "?text=" . urlencode("Halo, ada tamu atas nama {$record->nama} dari {$record->asal_instansi} menunggu di depan. Keperluan: {$record->keperluan}"))
                    ->openUrlInNewTab()
                    ->visible(fn(Tamu $record): bool => filled($record->pegawai_id)),

                // Tombol Pulang (Icon Only)
                Action::make('pulang')
                    ->hiddenLabel()
                    ->tooltip('Tamu Pulang')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn(Tamu $record) => $record->update(['waktu_keluar' => now()]))
                    ->hidden(fn(Tamu $record): bool => filled($record->waktu_keluar)),

                // Tombol View (Icon Only)
                ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('Detail Tamu')
                    ->icon('heroicon-m-eye')
                    ->color('info'),

                // Tombol Edit (Icon Only)
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Edit Data')
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary'),

                // Tombol Delete (Icon Only)
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Hapus Data')
                    ->icon('heroicon-m-trash')
                    ->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
