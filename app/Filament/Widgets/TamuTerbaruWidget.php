<?php

namespace App\Filament\Widgets;

use App\Models\Tamu;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Storage;

class TamuTerbaruWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Tamu Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(Tamu::query()->latest('created_at')->limit(5))
            ->columns([
                ImageColumn::make('foto_selfie')
                    ->label('Foto')
                    ->circular()
                    ->getStateUsing(fn(Tamu $record) => static::resolveImage($record->foto_selfie)),

                ImageColumn::make('tanda_tangan')
                    ->label('TTD')
                    ->getStateUsing(fn(Tamu $record) => static::resolveImage($record->tanda_tangan)),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M, H:i'),

                TextColumn::make('nama')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('asal_instansi')
                    ->placeholder('-'),

                TextColumn::make('pegawai.nama')
                    ->label('Menemui')
                    ->placeholder('Belum ditentukan'),

                TextColumn::make('waktu_keluar')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Sudah Pulang' : 'Masih di Lokasi')
                    ->color(fn($state) => $state ? 'success' : 'warning'),
            ])
            ->recordActions([
                Action::make('pulang')
                    ->hiddenLabel()
                    ->tooltip('Tandai Pulang')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Tamu $record) {
                        $record->update(['waktu_keluar' => now()]);
                        Notification::make()
                            ->title('Tamu ditandai sudah pulang')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tamu $record): bool => blank($record->waktu_keluar)),
            ])
            ->paginated(false);
    }

    protected static function resolveImage(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'data:image')) {
            return $value;
        }

        foreach (['public', 'local'] as $diskName) {
            $disk = Storage::disk($diskName);
            if ($disk->exists($value)) {
                return 'data:' . $disk->mimeType($value) . ';base64,' . base64_encode($disk->get($value));
            }
        }

        return null;
    }
}
