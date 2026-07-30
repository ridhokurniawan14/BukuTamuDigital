<?php

namespace App\Filament\Resources\Pengaturans;

use App\Filament\Resources\Pengaturans\Pages\EditPengaturan;
use App\Filament\Resources\Pengaturans\Schemas\PengaturanForm;
use App\Models\Pengaturan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PengaturanResource extends Resource
{
    protected static ?string $model = Pengaturan::class;

    // Ganti Label dan Icon
    protected static ?string $modelLabel = 'Pengaturan Aplikasi';
    protected static ?string $pluralModelLabel = 'Pengaturan Aplikasi';
    protected static ?string $navigationLabel = 'Pengaturan';
    protected static ?int $navigationSort = 99;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth; // Icon Roda Gigi

    public static function getNavigationGroup(): ?string
    {
        return 'Sistem';
    }

    public static function form(Schema $schema): Schema
    {
        return PengaturanForm::configure($schema);
    }

    // FUNGSI TABLE DIHAPUS KARENA TIDAK PERLU!

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            // JURUS SAKTI: Saat menu diklik, langsung buka halaman Edit!
            'index' => EditPengaturan::route('/'),
        ];
    }
}
