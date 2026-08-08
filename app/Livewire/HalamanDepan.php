<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Pengaturan;
use App\Models\Pegawai;
use App\Models\Tamu;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    // Variabel Media
    public $foto_selfie;
    public $tanda_tangan;

    // Variabel Tampilan
    public $buka_form = true;
    public $pesan_tutup = '';
    public $is_success = false;

    // === ANTI-SPAM/BOT ===
    public $website = '';       // Honeypot: field jebakan, HARUS kosong. Jangan dipakai user.
    public $formDibukaPada;     // Time-trap: catat kapan form dibuka.

    public function mount()
    {
        $this->formDibukaPada = now();

        if (Schema::hasTable('pengaturans')) {
            $this->pengaturan = Pengaturan::first();
        }
        if (Schema::hasTable('pegawais')) {
            $this->daftarPegawai = Pegawai::orderBy('nama', 'asc')->get();
        }

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

    // JURUS PERBAIKAN: Fungsi ini sekarang bisa menerima folder kosong (root)
    private function simpanGambarBase64($base64String, $folder = '')
    {
        if (!$base64String) return null;

        preg_match('/^data:image\/(\w+);base64,/', $base64String, $type);
        $extension = strtolower($type[1] ?? 'png');
        $extension = $extension == 'jpeg' ? 'jpg' : $extension;

        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
        $image = str_replace(' ', '+', $image);

        // Jika foldernya diset, beri tanda '/' di belakangnya. Jika tidak, kosongkan.
        $path = $folder ? $folder . '/' : '';
        $fileName = $path . Str::random(32) . '.' . $extension;

        Storage::disk('public')->put($fileName, base64_decode($image));

        return $fileName;
    }

    public function simpanData()
    {
        // === CEK 1: HONEYPOT ===
        // Kalau field jebakan ini keisi, hampir pasti itu bot.
        // Kita pura-pura sukses biar bot gak curiga terus coba lagi.
        if (!empty($this->website)) {
            $this->is_success = true;
            return;
        }

        // === CEK 2: TIME-TRAP ===
        // Manusia butuh waktu buat isi 3 step + foto + TTD.
        // Kalau submit kurang dari 5 detik dari form dibuka, tolak.
        if ($this->formDibukaPada && now()->diffInSeconds($this->formDibukaPada) < 5) {
            $this->addError('nama', 'Terlalu cepat! Silakan isi form dengan wajar.');
            return;
        }

        $this->validate([
            'nama' => 'required|min:3',
            'alamat' => 'required',
            'no_hp' => ['required', 'regex:/^[0-9]{9,15}$/'],
            'kategori_keperluan' => 'required',
            'detail_keperluan' => 'required',
            'foto_selfie' => 'required',
            'tanda_tangan' => 'required',
        ], [
            'no_hp.regex' => 'Nomor HP hanya boleh berisi angka (9-15 digit).',
        ]);

        if ($this->kategori_keperluan === 'Menemui Guru / Pegawai / Kepsek' && empty($this->pegawai_id)) {
            $this->addError('pegawai_id', 'Nama Guru / Pegawai wajib dipilih!');
            return;
        }

        // === CEK 3: RATE LIMIT (kombinasi IP + no_hp) ===
        $ipAddress = request()->ip();
        $rateLimitKey = 'submit-tamu-' . $ipAddress;
        $rateLimitKeyHp = 'submit-tamu-hp-' . $this->no_hp;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 8)) {
            $waktuTunggu = RateLimiter::availableIn($rateLimitKey);
            $menitTunggu = ceil($waktuTunggu / 60);
            session()->flash('error', "Terlalu banyak percobaan dari jaringan ini! Silakan tunggu {$menitTunggu} menit lagi.");
            return;
        }

        if (RateLimiter::tooManyAttempts($rateLimitKeyHp, 2)) {
            $waktuTunggu = RateLimiter::availableIn($rateLimitKeyHp);
            $menitTunggu = ceil($waktuTunggu / 60);
            session()->flash('error', "Nomor HP ini sudah mengisi buku tamu baru-baru ini. Silakan tunggu {$menitTunggu} menit lagi.");
            return;
        }

        RateLimiter::hit($rateLimitKey, 10 * 60);
        RateLimiter::hit($rateLimitKeyHp, 30 * 60);

        // =======================================================
        // SIMPAN KE MySQL (Jalurnya Mengikuti Filament Admin)
        // =======================================================
        Tamu::create([
            'nama' => $this->nama,
            'asal_instansi' => $this->asal_instansi == "" ? null : $this->asal_instansi,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'kategori_keperluan' => $this->kategori_keperluan,
            'keperluan' => $this->detail_keperluan,
            'pegawai_id' => $this->pegawai_id == "" ? null : $this->pegawai_id,

            // SINKRONISASI FOLDER DENGAN TAMUFORM.PHP:
            'foto_selfie' => $this->simpanGambarBase64($this->foto_selfie, 'foto-tamu'),
            'tanda_tangan' => $this->simpanGambarBase64($this->tanda_tangan, 'tanda-tangan'),
        ]);

        $this->is_success = true;
    }

    public function resetForm()
    {
        $this->reset(['nama', 'asal_instansi', 'alamat', 'no_hp', 'kategori_keperluan', 'detail_keperluan', 'pegawai_id', 'foto_selfie', 'tanda_tangan']);
        $this->formDibukaPada = now(); // reset juga time-trap-nya buat submit berikutnya
        $this->is_success = false;
    }

    public function render()
    {
        $judul = $this->pengaturan->nama_aplikasi ?? 'Buku Tamu Digital';
        return view('livewire.halaman-depan')->title($judul);
    }
}
