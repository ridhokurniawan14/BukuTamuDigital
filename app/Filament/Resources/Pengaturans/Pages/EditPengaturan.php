<?php

namespace App\Filament\Resources\Pengaturans\Pages;

use App\Filament\Resources\Pengaturans\PengaturanResource;
use App\Models\Pengaturan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengaturan extends EditRecord
{
    protected static string $resource = PengaturanResource::class;

    // JURUS SAKTI: Memaksa selalu membuka / membuat data ID = 1
    public function mount(int | string $record = 1): void
    {
        // Pastikan di database sudah ada baris data pengaturan. Jika kosong, buatkan otomatis.
        Pengaturan::firstOrCreate(['id' => 1]);

        // Panggil data ID 1 tersebut ke dalam form
        parent::mount(1);
    }

    // Opsional: Sembunyikan tombol delete karena pengaturan tidak boleh dihapus
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Opsional: Ubah pesan notifikasi saat berhasil disimpan
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengaturan berhasil diperbarui!';
    }
}
