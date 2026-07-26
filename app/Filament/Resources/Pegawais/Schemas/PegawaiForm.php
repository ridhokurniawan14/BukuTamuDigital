<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PegawaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->placeholder('Contoh: Budi Santoso, S.Pd.')
                    ->helperText('Masukkan nama lengkap beserta gelar (jika ada).')
                    ->required(),

                Select::make('jabatan')
                    ->options([
                        'Kepala Sekolah' => 'Kepala Sekolah',
                        'Waka Kurikulum' => 'Waka Kurikulum',
                        'Waka Kesiswaan' => 'Waka Kesiswaan',
                        'Waka Humas' => 'Waka Humas',
                        'Waka Sarana Prasarana' => 'Waka Sarana Prasarana',
                        'Bendahara' => 'Bendahara',
                        'K3 MPLB' => 'K3 MPLB',
                        'K3 AKL' => 'K3 AKL',
                        'K3 Pemasaran' => 'K3 Pemasaran',
                        'K3 Kuliner' => 'K3 Kuliner',
                        'K3 TKJ' => 'K3 TKJ',
                        'K3 Perhotelan' => 'K3 Perhotelan',
                        'Ketua TEFA' => 'Ketua TEFA',
                        'Pembina OSIS' => 'Pembina OSIS',
                        'Koordinator BK' => 'Koordinator BK',
                        'Koordinator BKK' => 'Koordinator BKK',
                        'Koordinator TU' => 'Koordinator TU',
                        'Operator Sekolah' => 'Operator Sekolah',
                        'Guru' => 'Guru',
                        'TU' => 'TU',
                    ])
                    ->placeholder('Pilih Jabatan')
                    ->helperText('Pilih jabatan yang sesuai dari daftar.')
                    ->searchable()
                    ->required(),

                TextInput::make('no_hp')
                    ->label('No. HP / WhatsApp')
                    ->placeholder('Contoh: 08123456789')
                    ->helperText('Nomor ini akan digunakan untuk notifikasi WhatsApp.')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->helperText('Matikan jika pegawai sudah pindah tugas atau pensiun.')
                    ->default(true)
                    ->required(),
            ]);
    }
}
