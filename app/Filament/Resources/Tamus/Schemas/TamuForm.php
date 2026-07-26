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
use Saade\FilamentAutograph\Forms\Components\SignaturePad; // Import plugin Tanda Tangan

class TamuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->placeholder('Contoh: Budi Santoso')
                    ->helperText('Masukkan nama lengkap tamu.')
                    ->required(),

                TextInput::make('asal_instansi')
                    ->placeholder('Contoh: PT. Maju Jaya / Universitas Brawijaya')
                    ->helperText('Kosongkan jika ini adalah kunjungan pribadi.'),

                TextInput::make('alamat')
                    ->placeholder('Contoh: Jl. Merdeka No.10, Kota X')
                    ->required(),

                TextInput::make('no_hp')
                    ->label('No. HP / WhatsApp')
                    ->placeholder('Contoh: 08123456789')
                    ->helperText('Pastikan nomor aktif dan bisa dihubungi via WA.')
                    ->required(),

                Select::make('kategori_keperluan')
                    ->options([
                        'Dinas / Kedinasan' => 'Dinas / Kedinasan',
                        'Orang Tua / Wali Murid' => 'Orang Tua / Wali Murid',
                        'Menemui Guru / Pegawai / Kepsek' => 'Menemui Guru / Pegawai / Kepsek',
                        'Administrasi / Tata Usaha' => 'Administrasi / Tata Usaha',
                        'Studi Banding / Kerja Sama' => 'Studi Banding / Kerja Sama',
                        'Vendor / Sosialisasi' => 'Vendor / Sosialisasi',
                        'Pengantaran Barang' => 'Pengantaran Barang',
                        'Servis / Maintenance' => 'Servis / Maintenance',
                        'Alumni' => 'Alumni', // Dipisah
                        'Kegiatan Sekolah' => 'Kegiatan Sekolah', // Dipisah
                        'Lainnya' => 'Lainnya',
                    ])
                    ->helperText('Pilih kategori yang paling sesuai dengan tujuan kedatangan.')
                    ->live()
                    ->required(),

                Select::make('pegawai_id')
                    ->relationship('pegawai', 'nama')
                    ->label('Pilih Nama Guru / Pegawai')
                    // Memunculkan Nama beserta Jabatannya di dropdown
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama} - ({$record->jabatan})")
                    ->searchable(['nama', 'jabatan'])
                    ->preload()
                    ->helperText('Ketik nama atau jabatan untuk mencari.')
                    ->visible(fn(Get $get) => $get('kategori_keperluan') === 'Menemui Guru / Pegawai / Kepsek')
                    ->required(fn(Get $get) => $get('kategori_keperluan') === 'Menemui Guru / Pegawai / Kepsek'),

                Textarea::make('keperluan')
                    ->label('Detail Keperluan / Keterangan')
                    ->placeholder(fn(Get $get) => $get('kategori_keperluan') === 'Orang Tua / Wali Murid'
                        ? 'Contoh: Menjemput anak Budi kelas 10A (sakit)'
                        : 'Tuliskan detail keperluan di sini...')
                    ->helperText('Berikan keterangan singkat dan jelas mengenai tujuan kedatangan.')
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('foto_selfie')
                    ->label('Foto Selfie Tamu')
                    ->image()
                    ->directory('foto-tamu')
                    ->helperText('Pastikan wajah terlihat jelas dan tidak memakai masker/kacamata hitam.')
                    ->required(),

                SignaturePad::make('tanda_tangan')
                    ->label('Tanda Tangan Digital')
                    ->dotSize(2.0)
                    ->lineMinWidth(1.0)
                    ->lineMaxWidth(2.5)
                    ->penColor('rgb(0, 0, 0)')
                    ->backgroundColor('#ffffff') // Gunakan Hex murni
                    // Tambahkan tanda seru (!) sebelum bg-white untuk memaksa warna putih sejak awal render
                    ->extraAttributes(['class' => '!bg-white border-2 border-gray-300 rounded-lg'])
                    ->helperText('Goreskan tanda tangan di dalam kotak di atas.')
                    ->required()
                    ->columnSpanFull(),

                Toggle::make('is_lsm')
                    ->label('Tandai sebagai LSM')
                    ->helperText('Centang jika tamu berasal dari Lembaga Swadaya Masyarakat.'),

                DateTimePicker::make('waktu_keluar')
                    ->label('Waktu Pulang')
                    ->helperText('Bisa diisi manual atau klik tombol "Tamu Pulang" di tabel depan.'),
            ]);
    }
}
