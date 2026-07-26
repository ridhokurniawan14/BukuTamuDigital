<?php

namespace App\Filament\Resources\Tamus\Tables;

use App\Models\Tamu;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
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
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Datang')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('nama')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('asal_instansi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('pegawai.nama')
                    ->label('Menemui')
                    ->searchable(),

                // Ini fitur ajaib Filament: Toggle bisa langsung di-klik dari tabel!
                ToggleColumn::make('is_lsm')
                    ->label('LSM?'),

                TextColumn::make('waktu_keluar')
                    ->label('Jam Keluar')
                    ->dateTime('H:i')
                    ->placeholder('Belum Pulang')
                    ->badge()
                    ->color(fn(string $state): string => 'success'),
            ])
            ->defaultSort('created_at', 'desc') // Mengurutkan otomatis dari yang terbaru
            ->filters([
                TernaryFilter::make('is_lsm')
                    ->label('Status LSM'),

                // Filter Rentang Tanggal Manual
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
            ->actions([
                // Tombol WA ke Pegawai
                Action::make('hubungi_wa')
                    ->label('WA Pegawai')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn(Tamu $record) => "https://wa.me/" . ($record->pegawai->no_hp ?? '') . "?text=" . urlencode("Halo, ada tamu atas nama {$record->nama} dari {$record->asal_instansi} menunggu di depan. Keperluan: {$record->keperluan}"))
                    ->openUrlInNewTab()
                    ->visible(fn(Tamu $record): bool => filled($record->pegawai_id)),

                // Tombol Tamu Pulang
                Action::make('pulang')
                    ->label('Tamu Pulang')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn(Tamu $record) => $record->update(['waktu_keluar' => now()]))
                    ->hidden(fn(Tamu $record): bool => filled($record->waktu_keluar)),

                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
