<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Pengaturan;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter; // Jurus Anti-Spam
use Carbon\Carbon; // Jurus Waktu

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

    // Variabel Keamanan
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

        // Jurus mapping nama hari dari bahasa Inggris ke Indonesia yang anti-gagal
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

        // Ambil pengaturan dari Database
        $jamBuka = $this->pengaturan->jam_buka ?? '06:00:00';
        $jamTutup = $this->pengaturan->jam_tutup ?? '18:00:00';
        $hariKerja = $this->pengaturan->hari_kerja ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $jamBukaDisplay = Carbon::parse($jamBuka)->format('H:i');
        $jamTutupDisplay = Carbon::parse($jamTutup)->format('H:i');

        // LOGIKA 1: Cek apakah hari ini adalah Hari Libur
        if (!in_array($hariSekarang, $hariKerja)) {
            $this->buka_form = false;
            $this->pesan_tutup = "Mohon maaf, layanan Buku Tamu sedang tutup. Kami hanya melayani kunjungan pada hari kerja (" . implode(', ', $hariKerja) . ").";
        }
        // LOGIKA 2: Cek apakah jam saat ini di luar Jam Operasional
        elseif ($jamSekarang < $jamBuka || $jamSekarang > $jamTutup) {
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

    // FUNGSI SIMPAN (Dengan Anti Spam)
    public function simpanData()
    {
        // ==========================================
        // FITUR ANTI SPAM & BOT (Max 3x per 5 Menit per IP)
        // ==========================================
        $ipAddress = request()->ip();
        $rateLimitKey = 'submit-tamu-' . $ipAddress;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $waktuTunggu = RateLimiter::availableIn($rateLimitKey);
            session()->flash('error', "Terlalu banyak permintaan! Silakan coba lagi dalam {$waktuTunggu} detik.");
            return;
        }

        RateLimiter::hit($rateLimitKey, 5 * 60); // Kunci selama 5 menit (300 detik)

        // (NANTI DISINI TEMPAT MENYIMPAN KE DATABASE)

        session()->flash('success', 'Data berhasil diverifikasi! Nanti akan masuk ke database.');
    }

    public function render()
    {
        $judul = $this->pengaturan->nama_aplikasi ?? 'Buku Tamu Digital';
        return view('livewire.halaman-depan')->title($judul);
    }
}
