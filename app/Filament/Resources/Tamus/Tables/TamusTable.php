<?php

namespace App\Filament\Resources\Tamus\Tables;

use App\Models\Tamu;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select; // WAJIB untuk pop-up pilih pegawai
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString; // WAJIB untuk merender foto asli
use Illuminate\Support\Facades\Storage; // WAJIB untuk mengambil URL foto

class TamusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                // 1. REVISI: Foto Selfie kebal URL patah dengan jurus Base64
                ImageColumn::make('foto_selfie')
                    ->label('Foto')
                    ->circular()
                    ->tooltip('Klik untuk lihat foto asli')
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->action(
                        Action::make('view_foto')
                            ->modalHeading('Foto Selfie Tamu')
                            ->modalSubmitAction(false) // Hilangkan tombol "Submit"
                            ->modalCancelAction(fn($action) => $action->label('Tutup'))
                            ->modalContent(function (Tamu $record) {
                                // Cari tahu brankas disk apa yang dipakai server
                                $disk = Storage::disk(config('filament.default_filesystem_disk', 'public'));

                                // Ambil file fisik langsung, ubah jadi teks gambar murni (Base64)
                                if ($record->foto_selfie && $disk->exists($record->foto_selfie)) {
                                    $mime = $disk->mimeType($record->foto_selfie);
                                    $base64 = base64_encode($disk->get($record->foto_selfie));
                                    $url = 'data:' . $mime . ';base64,' . $base64;
                                } else {
                                    $url = url('/images/default-avatar.png');
                                }

                                // Hapus w-full dan tambahkan width: auto di style
                                // Tambahkan object-fit: contain; di dalam style
                                return new HtmlString('<img src="' . $url . '" style="max-height: 60vh; max-width: 100%; object-fit: contain;" class="mx-auto rounded-xl shadow-md border border-gray-300 dark:border-gray-700" />');
                            })
                    ),
                TextColumn::make('created_at')
                    ->label('Waktu Datang')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('nama')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('kategori_keperluan')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('asal_instansi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('pegawai.nama')
                    ->label('Menemui')
                    ->placeholder('Belum ditentukan') // Tulisan yang muncul saat kosong
                    ->searchable(),

                ToggleColumn::make('is_lsm')
                    ->label('LSM?'),

                TextColumn::make('waktu_keluar')
                    ->label('Jam Keluar')
                    ->placeholder('Belum Pulang')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') . ' WIB' : null)
                    ->color(fn($state) => $state === null ? 'danger' : 'success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_lsm')
                    ->label('Status LSM'),
                TrashedFilter::make(),
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal'),
                        DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                // TOMBOL BARU: EXPORT DATA
                Action::make('export_laporan')
                    ->label('Export Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->modalHeading('Export Laporan Tamu')
                    ->modalDescription('Pilih rentang tanggal dan format laporan yang ingin diunduh.')
                    ->form([
                        Select::make('format')
                            ->label('Format File')
                            ->options([
                                'excel' => 'Excel (.xls)',
                                'pdf' => 'PDF',
                            ])
                            ->default('excel')
                            ->required(),
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth())
                            ->required(),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->default(now()->endOfDay())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $tamus = Tamu::with('pegawai')
                            ->whereBetween('created_at', [
                                $data['dari_tanggal'] . ' 00:00:00',
                                $data['sampai_tanggal'] . ' 23:59:59',
                            ])
                            ->orderBy('created_at', 'asc')
                            ->get();

                        $disk = \Illuminate\Support\Facades\Storage::disk(config('filament.default_filesystem_disk', 'public'));
                        $periodeDari = \Carbon\Carbon::parse($data['dari_tanggal'])->translatedFormat('d M Y');
                        $periodeSampai = \Carbon\Carbon::parse($data['sampai_tanggal'])->translatedFormat('d M Y');

                        // ==========================================
                        // 1. JIKA MEMILIH EXCEL (.xlsx ASLI DENGAN GAMBAR)
                        // ==========================================
                        if ($data['format'] === 'excel') {
                            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                            $sheet = $spreadsheet->getActiveSheet();

                            // Set Judul
                            $sheet->mergeCells('A1:I1');
                            $sheet->setCellValue('A1', 'Laporan Buku Tamu Digital');
                            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
                            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                            $sheet->mergeCells('A2:I2');
                            $sheet->setCellValue('A2', 'Periode: ' . $periodeDari . ' s/d ' . $periodeSampai);
                            $sheet->getStyle('A2')->getFont()->setSize(12);
                            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                            // Header Tabel
                            $headers = ['No', 'Waktu Datang', 'Nama Tamu', 'Asal Instansi', 'Keperluan', 'Menemui', 'Jam Keluar', 'Foto Selfie', 'Tanda Tangan'];
                            $sheet->fromArray($headers, null, 'A4');
                            $sheet->getStyle('A4:I4')->getFont()->setBold(true);
                            $sheet->getStyle('A4:I4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
                            $sheet->getStyle('A4:I4')->getAlignment()->setHorizontal('center');

                            // Lebar Kolom
                            $sheet->getColumnDimension('B')->setWidth(18);
                            $sheet->getColumnDimension('C')->setWidth(20);
                            $sheet->getColumnDimension('D')->setWidth(20);
                            $sheet->getColumnDimension('E')->setWidth(30);
                            $sheet->getColumnDimension('F')->setWidth(25);
                            $sheet->getColumnDimension('G')->setWidth(15);
                            $sheet->getColumnDimension('H')->setWidth(15);
                            $sheet->getColumnDimension('I')->setWidth(15);

                            $rowNum = 5;
                            $no = 1;

                            foreach ($tamus as $tamu) {
                                // Lebarkan tinggi baris agar gambar muat
                                $sheet->getRowDimension($rowNum)->setRowHeight(80);

                                $sheet->setCellValue('A' . $rowNum, $no++);
                                $sheet->setCellValue('B' . $rowNum, $tamu->created_at->format('d M Y') . "\n" . $tamu->created_at->format('H:i') . ' WIB');
                                $sheet->getStyle('B' . $rowNum)->getAlignment()->setWrapText(true); // Agar jam bisa turun ke bawah

                                $sheet->setCellValue('C' . $rowNum, $tamu->nama);
                                $sheet->setCellValue('D' . $rowNum, $tamu->asal_instansi ?? '-');
                                $sheet->setCellValue('E' . $rowNum, $tamu->keperluan);
                                $sheet->setCellValue('F' . $rowNum, $tamu->pegawai?->nama ?? '-');
                                $sheet->setCellValue('G' . $rowNum, $tamu->waktu_keluar ? \Carbon\Carbon::parse($tamu->waktu_keluar)->format('H:i') . ' WIB' : '-');

                                // MASUKKAN FOTO SELFIE FISIK KE DALAM EXCEL
                                if ($tamu->foto_selfie && $disk->exists($tamu->foto_selfie)) {
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    $drawing->setName('Foto');
                                    $drawing->setPath($disk->path($tamu->foto_selfie));
                                    $drawing->setCoordinates('H' . $rowNum);
                                    $drawing->setHeight(90);
                                    $drawing->setOffsetX(10);
                                    $drawing->setOffsetY(10);
                                    $drawing->setWorksheet($sheet);
                                }

                                // MASUKKAN TANDA TANGAN KE DALAM EXCEL
                                if ($tamu->tanda_tangan) {
                                    if (str_starts_with($tamu->tanda_tangan, 'data:image')) {
                                        // Ubah Base64 menjadi gambar memori khusus untuk Excel
                                        $base64data = substr($tamu->tanda_tangan, strpos($tamu->tanda_tangan, ',') + 1);
                                        $image = imagecreatefromstring(base64_decode($base64data));
                                        if ($image !== false) {
                                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
                                            $drawing->setName('TTD');
                                            $drawing->setImageResource($image);
                                            $drawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_PNG);
                                            $drawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG);
                                            $drawing->setCoordinates('I' . $rowNum);
                                            $drawing->setHeight(80);
                                            $drawing->setOffsetX(10);
                                            $drawing->setOffsetY(10);
                                            $drawing->setWorksheet($sheet);
                                        }
                                    } else if ($disk->exists($tamu->tanda_tangan)) {
                                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                        $drawing->setName('TTD');
                                        $drawing->setPath($disk->path($tamu->tanda_tangan));
                                        $drawing->setCoordinates('I' . $rowNum);
                                        $drawing->setHeight(80);
                                        $drawing->setOffsetX(10);
                                        $drawing->setOffsetY(10);
                                        $drawing->setWorksheet($sheet);
                                    }
                                }
                                $rowNum++;
                            }

                            // Berikan Border (Garis Kotak) ke seluruh tabel
                            $styleArray = [
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                ],
                                'alignment' => [
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                ],
                            ];
                            $sheet->getStyle('A4:I' . ($rowNum - 1))->applyFromArray($styleArray);
                            $sheet->getStyle('A4:A' . ($rowNum - 1))->getAlignment()->setHorizontal('center');
                            $sheet->getStyle('G4:G' . ($rowNum - 1))->getAlignment()->setHorizontal('center');

                            // Simpan & Download
                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                            $filename = 'Laporan_Buku_Tamu_' . date('Ymd') . '.xlsx';
                            $tempFile = tempnam(sys_get_temp_dir(), 'excel');
                            $writer->save($tempFile);

                            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
                        }

                        // ==========================================
                        // 2. JIKA MEMILIH PDF (TIDAK ADA PERUBAHAN)
                        // ==========================================
                        if ($data['format'] === 'pdf') {
                            $html = '<!DOCTYPE html><html><head>';
                            $html .= '<style>
                                        body { font-family: sans-serif; font-size: 11px; margin-bottom: 20px; }
                                        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                                        th, td { border: 1px solid #444; padding: 6px; text-align: left; vertical-align: middle; }
                                        th { background-color: #e2e8f0; text-align: center; font-weight: bold; }
                                        .center { text-align: center; }
                                      </style>';
                            $html .= '</head><body>';

                            $html .= '<script type="text/php">
                                        if (isset($pdf)) {
                                            $x = 740;
                                            $y = 570;
                                            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
                                            $font = $fontMetrics->get_font("sans-serif", "normal");
                                            $size = 9;
                                            $color = array(0,0,0);
                                            $pdf->page_text($x, $y, $text, $font, $size, $color);
                                        }
                                      </script>';

                            $html .= '<h2 class="center" style="margin-bottom:0; font-size: 22px;">Laporan Buku Tamu Digital</h2>';
                            $html .= '<p class="center" style="margin-top:5px; color:#555; font-size: 14px;">Periode: ' . $periodeDari . ' s/d ' . $periodeSampai . '</p>';

                            $html .= '<table>';
                            $html .= '<thead><tr>
                                        <th width="3%">No</th>
                                        <th width="12%">Waktu</th>
                                        <th width="14%">Nama Tamu</th>
                                        <th width="12%">Instansi</th>
                                        <th width="15%">Keperluan</th>
                                        <th width="10%">Menemui</th>
                                        <th width="8%">Keluar</th>
                                        <th width="12%">Foto</th>
                                        <th width="14%">TTD</th>
                                      </tr></thead><tbody>';

                            $no = 1;
                            foreach ($tamus as $tamu) {
                                $fotoImg = '-';
                                if ($tamu->foto_selfie && $disk->exists($tamu->foto_selfie)) {
                                    $base64 = base64_encode($disk->get($tamu->foto_selfie));
                                    $mime = $disk->mimeType($tamu->foto_selfie);
                                    $fotoImg = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="width: 75px; height: 75px; object-fit: cover; border-radius:6px;">';
                                }

                                $ttdImg = '-';
                                if ($tamu->tanda_tangan) {
                                    if (str_starts_with($tamu->tanda_tangan, 'data:image')) {
                                        $ttdImg = '<img src="' . $tamu->tanda_tangan . '" style="width: 90px; height: auto;">';
                                    } else if ($disk->exists($tamu->tanda_tangan)) {
                                        $base64 = base64_encode($disk->get($tamu->tanda_tangan));
                                        $mime = $disk->mimeType($tamu->tanda_tangan);
                                        $ttdImg = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="width: 90px; height: auto;">';
                                    }
                                }

                                $html .= '<tr>';
                                $html .= '<td class="center">' . $no++ . '</td>';
                                $html .= '<td class="center">' . $tamu->created_at->format('d M Y') . '<br>' . $tamu->created_at->format('H:i') . ' WIB</td>';
                                $html .= '<td>' . $tamu->nama . '</td>';
                                $html .= '<td>' . ($tamu->asal_instansi ?? '-') . '</td>';
                                $html .= '<td>' . $tamu->keperluan . '</td>';
                                $html .= '<td>' . ($tamu->pegawai?->nama ?? '-') . '</td>';
                                $html .= '<td class="center">' . ($tamu->waktu_keluar ? \Carbon\Carbon::parse($tamu->waktu_keluar)->format('H:i') . ' WIB' : '-') . '</td>';
                                $html .= '<td class="center" style="padding: 10px 0;">' . $fotoImg . '</td>';
                                $html .= '<td class="center" style="padding: 10px 0;">' . $ttdImg . '</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</tbody></table></body></html>';

                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                                ->setPaper('a4', 'landscape')
                                ->setOptions(['isPhpEnabled' => true]);

                            return response()->streamDownload(fn() => print($pdf->output()), 'Laporan_Buku_Tamu_' . date('Ymd') . '.pdf');
                        }
                    }),
                // Tombol aslinya biarkan tetap di sini
                CreateAction::make()
                    ->label('Tambah Tamu')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary'),
            ])
            ->recordActions([
                // 2. REVISI: Tombol Sakti "Temui Pegawai"
                Action::make('temui_pegawai')
                    ->hiddenLabel()
                    ->tooltip('Tentukan Pegawai yang Ditemui')
                    ->icon('heroicon-m-user-plus')
                    ->color('primary')
                    // Keajaibannya: Tombol ini HANYA MUNCUL kalau pegawai_id kosong!
                    ->visible(fn(Tamu $record): bool => blank($record->pegawai_id))
                    ->modalHeading('Pilih Guru / Pegawai')
                    ->form([
                        Select::make('pegawai_id')
                            ->label('Pilih Guru / Pegawai')
                            ->relationship(
                                name: 'pegawai',
                                titleAttribute: 'nama',
                                modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nama} - ({$record->jabatan})")
                            ->searchable(['nama', 'jabatan'])
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (Tamu $record, array $data) {
                        // Proses Auto-Update data (tanpa perlu ke halaman Edit)
                        $record->update([
                            'pegawai_id' => $data['pegawai_id'],
                            'kategori_keperluan' => 'Menemui Guru / Pegawai / Kepsek',
                        ]);

                        // Munculkan notif sukses
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil Disimpan!')
                            ->body('Kategori otomatis berubah menjadi Menemui Pegawai.')
                            ->success()
                            ->send();
                    }),

                Action::make('hubungi_wa')
                    ->hiddenLabel()
                    ->tooltip('WA Pegawai')
                    ->icon('heroicon-m-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(function (Tamu $record) {
                        // Merakit kata-kata dinamis
                        $namaPegawai = $record->pegawai->nama ?? 'Bapak/Ibu';
                        $instansi = $record->asal_instansi ? " dari " . $record->asal_instansi : "";

                        // Teks sesuai format baru
                        $pesan = "Assalamualaikum Bapak/Ibu {$namaPegawai} ada tamu atas nama {$record->nama}{$instansi} menunggu di Front Office. Keperluan: {$record->keperluan}. Mohon Ketersediaannya menemui nggeh Bapak/Ibu. Terima kasih.";

                        return "https://wa.me/" . ($record->pegawai->no_hp ?? '') . "?text=" . urlencode($pesan);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn(Tamu $record): bool => filled($record->pegawai_id)),

                Action::make('pulang')
                    ->hiddenLabel()
                    ->tooltip('Tamu Pulang')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn(Tamu $record) => $record->update(['waktu_keluar' => now()]))
                    ->hidden(fn(Tamu $record): bool => filled($record->waktu_keluar)),

                ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('Detail Tamu')
                    ->icon('heroicon-m-eye')
                    ->color('info'),

                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Edit Data')
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary'),

                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Hapus Data')
                    ->icon('heroicon-m-trash')
                    ->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
