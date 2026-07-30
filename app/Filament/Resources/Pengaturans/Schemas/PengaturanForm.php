<?php

namespace App\Filament\Resources\Pengaturans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PengaturanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('PengaturanTabs')
                    ->tabs([
                        // ==========================================
                        // TAB 1: INFORMASI INSTANSI
                        // ==========================================
                        Tab::make('Informasi Instansi')
                            ->icon('heroicon-m-building-office-2')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('nama_aplikasi')
                                        ->label('Nama Aplikasi')
                                        ->required()
                                        ->default('Buku Tamu Digital'),

                                    TextInput::make('nama_instansi')
                                        ->label('Nama Instansi / Sekolah')
                                        ->required()
                                        ->placeholder('Contoh: SMKS PGRI 1 Giri'),

                                    TextInput::make('singkatan_instansi')
                                        ->label('Singkatan Instansi')
                                        ->required()
                                        ->placeholder('Contoh: SMK Grisawangi'),
                                ]),

                                Textarea::make('alamat_instansi')
                                    ->label('Alamat Lengkap Instansi')
                                    ->placeholder('Masukkan alamat lengkap untuk keperluan cetak kop surat laporan...')
                                    ->columnSpanFull(),
                            ]),

                        // ==========================================
                        // TAB 2: BRANDING & VISUAL
                        // ==========================================
                        Tab::make('Branding & Visual')
                            ->icon('heroicon-m-paint-brush')
                            ->schema([
                                ColorPicker::make('warna_utama')
                                    ->label('Warna Tema Utama')
                                    ->required()
                                    ->default('#f59e0b'),

                                Grid::make(3)->schema([
                                    FileUpload::make('logo_instansi')
                                        ->label('Logo Instansi')
                                        ->image()
                                        ->disk('public')
                                        ->directory('pengaturan'),

                                    FileUpload::make('favicon')
                                        ->label('Favicon (Icon Tab Browser)')
                                        ->image()
                                        ->disk('public')
                                        ->directory('pengaturan'),

                                    FileUpload::make('gambar_background')
                                        ->label('Background Halaman Depan')
                                        ->image()
                                        ->disk('public')
                                        ->directory('pengaturan'),
                                ]),
                            ]),

                        // ==========================================
                        // TAB 3: PREFERENSI SISTEM
                        // ==========================================
                        Tab::make('Preferensi Sistem')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Textarea::make('pesan_sambutan')
                                    ->label('Pesan Sambutan (Di Kiosk/Tablet)')
                                    ->placeholder('Contoh: Selamat Datang di SMKS 1 PGRI, Silakan isi data kunjungan Anda.')
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    Toggle::make('wajib_foto')
                                        ->label('Wajibkan Tamu Foto Selfie')
                                        ->default(true)
                                        ->required(),

                                    Toggle::make('wajib_ttd')
                                        ->label('Wajibkan Tamu Tanda Tangan')
                                        ->default(true)
                                        ->required(),
                                ]),
                                // Pengaturan Jam Operasional (Yang sudah ada)
                                Grid::make(2)->schema([
                                    \Filament\Forms\Components\TimePicker::make('jam_buka')
                                        ->label('Jam Buka Layanan Buku Tamu')
                                        ->default('06:00:00')
                                        ->required(),

                                    \Filament\Forms\Components\TimePicker::make('jam_tutup')
                                        ->label('Jam Tutup Layanan Buku Tamu')
                                        ->default('18:00:00')
                                        ->required(),
                                ]),

                                // JURUS BARU: Pemilihan Hari Kerja menggunakan Checkbox
                                \Filament\Forms\Components\CheckboxList::make('hari_kerja')
                                    ->label('Hari Kerja Operasional')
                                    ->options([
                                        'Senin' => 'Senin',
                                        'Selasa' => 'Selasa',
                                        'Rabu' => 'Rabu',
                                        'Kamis' => 'Kamis',
                                        'Jumat' => 'Jumat',
                                        'Sabtu' => 'Sabtu',
                                        'Minggu' => 'Minggu',
                                    ])
                                    ->default(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
                                    ->columns(4) // Biar rapi berjajar 4 kolom ke samping
                                    ->columnSpanFull()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull() // Memastikan tab membentang penuh ke samping
            ]);
    }
}
