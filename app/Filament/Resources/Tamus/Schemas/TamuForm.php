<?php

namespace App\Filament\Resources\Tamus\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TamuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),

                TextInput::make('asal_instansi')
                    ->default(null),

                TextInput::make('alamat')
                    ->required(),

                TextInput::make('no_hp')
                    ->required(),

                // 1. Kategori Keperluan kita ubah jadi Select
                Select::make('kategori_keperluan')
                    ->options([
                        'Bertemu Guru/Pegawai' => 'Bertemu Guru/Pegawai',
                        'Bertemu Siswa' => 'Bertemu Siswa',
                        'Dinas' => 'Dinas Terkait',
                        'Penawaran Barang/Jasa' => 'Penawaran Barang/Jasa',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->live() // Penting: agar form bisa merespon pilihan secara realtime
                    ->required(),

                // 2. Dropdown Pegawai HANYA muncul jika kategori yang dipilih adalah 'Bertemu Guru/Pegawai'
                Select::make('pegawai_id')
                    ->relationship('pegawai', 'nama')
                    ->label('Pilih Guru/Pegawai')
                    ->searchable()
                    ->preload()
                    ->visible(fn(Get $get) => $get('kategori_keperluan') === 'Bertemu Guru/Pegawai')
                    ->required(fn(Get $get) => $get('kategori_keperluan') === 'Bertemu Guru/Pegawai'),

                // 3. Kolom Keperluan menjadi tempat menulis detail (termasuk nama/kelas siswa)
                Textarea::make('keperluan')
                    ->label('Detail Keperluan / Keterangan')
                    ->placeholder(fn(Get $get) => $get('kategori_keperluan') === 'Bertemu Siswa'
                        ? 'Contoh: Menjemput anak atas nama Budi kelas 10A (sakit)'
                        : 'Tuliskan detail keperluan di sini...')
                    ->required()
                    ->columnSpanFull(),

                // 4. Ubah Selfie menjadi FileUpload khusus gambar
                FileUpload::make('foto_selfie')
                    ->label('Foto Selfie Tamu')
                    ->image()
                    ->directory('foto-tamu')
                    ->required(),

                Textarea::make('tanda_tangan')
                    ->required()
                    ->columnSpanFull(),

                Toggle::make('is_lsm')
                    ->label('Tandai sebagai LSM')
                    ->required(),

                DateTimePicker::make('waktu_keluar')
                    ->label('Waktu Pulang'),
            ]);
    }
}
