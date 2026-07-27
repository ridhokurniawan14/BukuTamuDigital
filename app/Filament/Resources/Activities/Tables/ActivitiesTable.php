<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
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
                    ->label('Tabel / Data')
                    ->formatStateUsing(fn($state) => class_basename($state))
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // TOMBOL BERSIHKAN LOG (Menyisakan 10 data terbaru)
                Action::make('clear_logs')
                    ->label('Bersihkan Log')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Bersihkan Log Aktivitas Lama')
                    ->modalDescription('Tindakan ini akan menghapus semua log aktivitas dan hanya menyisakan 10 log terbaru. Apakah Anda yakin?')
                    ->action(function () {
                        $latestIds = Activity::latest('id')->take(10)->pluck('id');
                        Activity::whereNotIn('id', $latestIds)->delete();

                        Notification::make()
                            ->title('Log Berhasil Dibersihkan!')
                            ->body('Semua log lama telah dihapus, menyisakan 10 aktivitas terbaru.')
                            ->success()
                            ->send();
                    })
            ])
            ->recordActions([
                // TOMBOL MATA: Membuka Pop-Up Detail Data
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading('Detail Perubahan Data')
                    ->mutateRecordDataUsing(function (array $data, Activity $record): array {
                        $data['new_data'] = $record->properties->get('attributes', []);
                        $data['old_data'] = $record->properties->get('old', []);
                        return $data;
                    })
                    ->schema([   // ganti dari ->form([...]) jadi ->schema([...])
                        Section::make('Data Baru (Setelah Disimpan)')
                            ->description('Ini adalah data yang saat ini masuk ke database.')
                            ->schema([
                                KeyValue::make('new_data')->label(''),
                            ])
                            ->visible(fn(Activity $record) => filled($record->properties->get('attributes'))),

                        Section::make('Data Lama (Sebelum Diubah)')
                            ->description('Ini adalah wujud asli data sebelum diedit oleh user.')
                            ->schema([
                                KeyValue::make('old_data')->label(''),
                            ])
                            ->visible(fn(Activity $record) => filled($record->properties->get('old'))),
                    ])
            ])
            ->bulkActions([
                // Kosong
            ]);
    }
}
