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
                    ->dateTime('H:i')
                    ->placeholder('Belum Pulang')
                    ->badge()
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
                                'excel' => 'Excel (CSV)',
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
                        // Tarik data dari database
                        $tamus = Tamu::with('pegawai')
                            ->whereBetween('created_at', [
                                $data['dari_tanggal'] . ' 00:00:00',
                                $data['sampai_tanggal'] . ' 23:59:59',
                            ])
                            ->orderBy('created_at', 'asc')
                            ->get();

                        // Panggil brankas disk penyimpanan (untuk akses gambar Base64 & URL)
                        $disk = \Illuminate\Support\Facades\Storage::disk(config('filament.default_filesystem_disk', 'public'));

                        // ==========================================
                        // 1. JIKA MEMILIH EXCEL (CSV)
                        // ==========================================
                        if ($data['format'] === 'excel') {
                            $filename = 'Laporan_Buku_Tamu_' . date('Ymd') . '.csv';
                            return response()->streamDownload(function () use ($tamus, $disk) {
                                echo "\xEF\xBB\xBF"; // BOM agar Excel tidak acak-acakan
                                $handle = fopen('php://output', 'w');

                                // Header Tabel (Menambahkan Link Foto & Link TTD)
                                fputcsv($handle, ['No', 'Waktu Datang', 'Nama Tamu', 'Asal Instansi', 'Kategori', 'Keperluan', 'Menemui', 'LSM', 'Jam Keluar', 'Link Foto Selfie', 'Link Tanda Tangan'], ';');

                                $no = 1;
                                foreach ($tamus as $tamu) {
                                    // Generate URL Penuh untuk Excel
                                    $fotoUrl = ($tamu->foto_selfie && $disk->exists($tamu->foto_selfie)) ? url($disk->url($tamu->foto_selfie)) : '-';
                                    $ttdUrl = ($tamu->tanda_tangan && $disk->exists($tamu->tanda_tangan)) ? url($disk->url($tamu->tanda_tangan)) : '-';

                                    fputcsv($handle, [
                                        $no++,
                                        $tamu->created_at->format('d M Y, H:i'),
                                        $tamu->nama,
                                        $tamu->asal_instansi ?? '-',
                                        $tamu->kategori_keperluan,
                                        $tamu->keperluan,
                                        $tamu->pegawai?->nama ?? '-',
                                        $tamu->is_lsm ? 'Ya' : 'Tidak',
                                        $tamu->waktu_keluar ? \Carbon\Carbon::parse($tamu->waktu_keluar)->format('H:i') : 'Belum Pulang',
                                        $fotoUrl, // Masuk ke Excel sebagai Teks Link
                                        $ttdUrl   // Masuk ke Excel sebagai Teks Link
                                    ], ';');
                                }
                                fclose($handle);
                            }, $filename, ['Content-Type' => 'text/csv']);
                        }

                        // ==========================================
                        // 2. JIKA MEMILIH PDF (Dengan Gambar & Page Number)
                        // ==========================================
                        if ($data['format'] === 'pdf') {
                            $html = '<!DOCTYPE html><html><head>';
                            // Styling Kertas PDF biar makin rapi dan muat banyak
                            $html .= '<style>
                                        body { font-family: sans-serif; font-size: 11px; margin-bottom: 20px; }
                                        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                                        th, td { border: 1px solid #444; padding: 6px; text-align: left; vertical-align: middle; }
                                        th { background-color: #e2e8f0; text-align: center; font-weight: bold; }
                                        .center { text-align: center; }
                                      </style>';
                            $html .= '</head><body>';

                            // SCRIPT AJAIB: Inject Page Number otomatis di pojok kanan bawah
                            $html .= '<script type="text/php">
                                        if (isset($pdf)) {
                                            $x = 740; // Posisi X (Pojok Kanan)
                                            $y = 570; // Posisi Y (Pojok Bawah)
                                            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
                                            $font = $fontMetrics->get_font("sans-serif", "normal");
                                            $size = 9;
                                            $color = array(0,0,0);
                                            $pdf->page_text($x, $y, $text, $font, $size, $color);
                                        }
                                      </script>';

                            $html .= '<h2 class="center" style="margin-bottom:0;">Laporan Buku Tamu Digital</h2>';
                            $html .= '<p class="center" style="margin-top:5px; color:#555;">Periode: ' . $data['dari_tanggal'] . ' s/d ' . $data['sampai_tanggal'] . '</p>';

                            // Header Tabel PDF dengan tambahan Foto dan TTD
                            $html .= '<table>';
                            $html .= '<thead><tr>
                                        <th width="3%">No</th>
                                        <th width="12%">Waktu</th>
                                        <th width="15%">Nama Tamu</th>
                                        <th width="12%">Instansi</th>
                                        <th width="18%">Keperluan</th>
                                        <th width="12%">Menemui</th>
                                        <th width="8%">Keluar</th>
                                        <th width="10%">Foto</th>
                                        <th width="10%">TTD</th>
                                      </tr></thead><tbody>';

                            $no = 1;
                            foreach ($tamus as $tamu) {
                                // Jurus Base64 untuk Foto agar lolos masuk ke PDF
                                $fotoImg = '-';
                                if ($tamu->foto_selfie && $disk->exists($tamu->foto_selfie)) {
                                    $base64 = base64_encode($disk->get($tamu->foto_selfie));
                                    $mime = $disk->mimeType($tamu->foto_selfie);
                                    $fotoImg = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="width: 45px; height: 45px; object-fit: cover; border-radius:4px;">';
                                }

                                // Jurus Base64 untuk Tanda Tangan
                                $ttdImg = '-';
                                if ($tamu->tanda_tangan && $disk->exists($tamu->tanda_tangan)) {
                                    $base64 = base64_encode($disk->get($tamu->tanda_tangan));
                                    $mime = $disk->mimeType($tamu->tanda_tangan);
                                    $ttdImg = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="width: 60px; height: auto;">';
                                }

                                $html .= '<tr>';
                                $html .= '<td class="center">' . $no++ . '</td>';
                                $html .= '<td>' . $tamu->created_at->format('d M Y, H:i') . '</td>';
                                $html .= '<td>' . $tamu->nama . '</td>';
                                $html .= '<td>' . ($tamu->asal_instansi ?? '-') . '</td>';
                                $html .= '<td>' . $tamu->keperluan . '</td>';
                                $html .= '<td>' . ($tamu->pegawai?->nama ?? '-') . '</td>';
                                $html .= '<td class="center">' . ($tamu->waktu_keluar ? \Carbon\Carbon::parse($tamu->waktu_keluar)->format('H:i') : '-') . '</td>';
                                $html .= '<td class="center">' . $fotoImg . '</td>';
                                $html .= '<td class="center">' . $ttdImg . '</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</tbody></table></body></html>';

                            // Wajib pakai setOptions(['isPhpEnabled' => true]) agar script Page Number berfungsi
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
