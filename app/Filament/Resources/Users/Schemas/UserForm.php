<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash; // Wajib untuk enkripsi password

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->placeholder('Contoh: Admin TU')
                    ->required(),

                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->placeholder('Contoh: admintu@sekolah.com')
                    ->unique(ignoreRecord: true) // Supaya email tidak boleh kembar
                    ->required(),

                // Dropdown untuk memilih Role (Super Admin, Admin TU, dll)
                Select::make('roles')
                    ->label('Hak Akses (Role)')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Pilih hak akses untuk pengguna ini.')
                    ->required(),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable() // Tombol mata untuk melihat password
                    // Password dienkripsi sebelum masuk database
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    // Password hanya dikirim ke database jika kolomnya diisi
                    ->dehydrated(fn($state) => filled($state))
                    // Password WAJIB diisi saat buat user baru, tapi OPSIONAL saat edit user
                    ->required(fn(string $context): bool => $context === 'create')
                    ->helperText('Kosongkan jika tidak ingin mengubah password (saat edit data).'),
            ]);
    }
}
