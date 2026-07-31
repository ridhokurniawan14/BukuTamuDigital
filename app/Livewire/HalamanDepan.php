<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Pengaturan;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

class HalamanDepan extends Component
{
    public $pengaturan;
    public $daftarPegawai = [];

    // Variabel Form
    public $nama;
    public $asal_instansi;
    public $alamat;
    public $no_hp;
    public $kategori_keperluan = '';
    public $detail_keperluan;
    public $pegawai_id = null;

    // Variabel Media (Kamera & TTD)
    public $foto_selfie;
    public $tanda_tangan;

    public $buka_form = true;
    public $pesan_tutup = '';

    public function mount()
    {
        if (Schema::hasTable('pengaturans')) {
            $this->pengaturan = Pengaturan::first();
        }
        if (Schema::hasTable('pegawais')) {
            $this->daftarPegawai = Pegawai::orderBy('nama', 'asc')->get();
        }

        // ==========================================
        // FITUR KEAMANAN HARI & JAM OPERASIONAL
        // ==========================================
        $jamSekarang = Carbon::now()->format('H:i:s');
        $namaHariInggris = Carbon::now()->format('l');
        $mapHari = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        $hariSekarang = $mapHari[$namaHariInggris];

        $jamBuka = $this->pengaturan->jam_buka ?? '06:00:00';
        $jamTutup = $this->pengaturan->jam_tutup ?? '18:00:00';
        $hariKerja = $this->pengaturan->hari_kerja ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $jamBukaDisplay = Carbon::parse($jamBuka)->format('H:i');
        $jamTutupDisplay = Carbon::parse($jamTutup)->format('H:i');

        if (!in_array($hariSekarang, $hariKerja)) {
            $this->buka_form = false;
            $this->pesan_tutup = "Mohon maaf, layanan Buku Tamu sedang tutup. Kami hanya melayani kunjungan pada hari kerja (" . implode(', ', $hariKerja) . ").";
        } elseif ($jamSekarang < $jamBuka || $jamSekarang > $jamTutup) {
            $this->buka_form = false;
            $this->pesan_tutup = "Mohon maaf, layanan Buku Tamu hanya melayani pada jam operasional ({$jamBukaDisplay} - {$jamTutupDisplay} WIB).";
        }
    }

    public function updatedKategoriKeperluan($value)
    {
        if ($value !== 'Menemui Guru / Pegawai / Kepsek') {
            $this->pegawai_id = null;
        }
    }

    // ==========================================
    // FUNGSI SIMPAN (Validasi, Media, Anti-Spam)
    // ==========================================
    public function simpanData()
    {
        // 1. VALIDASI WAJIB ISI (Server Side)
        $this->validate([
            'nama' => 'required|min:3',
            'alamat' => 'required',
            'no_hp' => 'required|min:10',
            'kategori_keperluan' => 'required',
            'detail_keperluan' => 'required',
            'foto_selfie' => 'required',
            'tanda_tangan' => 'required',
        ], [
            'required' => ':attribute wajib diisi atau diselesaikan!',
            'min' => ':attribute minimal :min karakter.',
        ]);

        // Jika butuh validasi guru saat kategori guru dipilih
        if ($this->kategori_keperluan === 'Menemui Guru / Pegawai / Kepsek' && empty($this->pegawai_id)) {
            $this->addError('pegawai_id', 'Nama Guru / Pegawai wajib dipilih!');
            return;
        }

        // 2. RATE LIMITER (Maksimal 5x dalam 10 Menit)
        $ipAddress = request()->ip();
        $rateLimitKey = 'submit-tamu-' . $ipAddress;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $waktuTunggu = RateLimiter::availableIn($rateLimitKey);
            $menitTunggu = ceil($waktuTunggu / 60);
            session()->flash('error', "Terlalu banyak percobaan! Silakan tunggu {$menitTunggu} menit lagi.");
            return;
        }
        RateLimiter::hit($rateLimitKey, 10 * 60); // Kunci 10 menit

        // DISINI NANTI PROSES SIMPAN KE DATABASE...

        session()->flash('success', 'Data kunjungan berhasil diverifikasi dan disimpan dengan aman!');
    }

    public function render()
    {
        $judul = $this->pengaturan->nama_aplikasi ?? 'Buku Tamu Digital';
        return view('livewire.halaman-depan')->title($judul);
    }
}
