<?php

namespace App\Filament\Imports;

use App\Models\Pegawai;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PegawaiImporter extends Importer
{
    protected static ?string $model = Pegawai::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Budi Santoso, S.Pd.'), // Contoh nama di template CSV

            ImportColumn::make('jabatan')
                ->requiredMapping()
                ->rules([
                    'required',
                    'in:Kepala Sekolah,Waka Kurikulum,Waka Kesiswaan,Waka Humas,Waka Sarana Prasarana,Bendahara,K3 MPLB,K3 AKL,K3 Pemasaran,K3 Kuliner,K3 TKJ,K3 Perhotelan,Ketua TEFA,Pembina OSIS,Koordinator BK,Koordinator BKK,Koordinator TU,Operator Sekolah,Guru,TU'
                ])
                // Memberikan teks petunjuk langsung di dalam template CSV
                ->example('Isi dengan: Kepala Sekolah / Guru / TU / Waka Kurikulum (harus sama persis)'),

            ImportColumn::make('no_hp')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('08123456789'), // Contoh no HP di template CSV

            // Kolom is_active DIHAPUS. 
            // Database otomatis akan memberikan nilai "true" / aktif untuk pegawai baru ini.
        ];
    }

    public function resolveRecord(): Pegawai
    {
        return Pegawai::firstOrNew([
            'nama' => $this->data['nama'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your pegawai import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
