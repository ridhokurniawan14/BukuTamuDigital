<div>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .animated-gradient-bg {
            background: linear-gradient(-45deg, #0f172a, #1e293b, var(--warna-utama), #0f172a);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .input-modern {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            color: #1e293b;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .input-modern:focus {
            background: #ffffff;
            border-color: var(--warna-utama);
            box-shadow: 0 0 0 4px rgba(var(--warna-rgb), 0.15);
            outline: none;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        ::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }
    </style>

    @php
        $bgUrl =
            $pengaturan && $pengaturan->gambar_background ? asset('storage/' . $pengaturan->gambar_background) : null;
        $logoUrl = $pengaturan && $pengaturan->logo_instansi ? asset('storage/' . $pengaturan->logo_instansi) : null;
        $warna = $pengaturan->warna_utama ?? '#f59e0b';
        echo "<style>:root { --warna-utama: {$warna}; --warna-rgb: 245, 158, 11; }</style>";
    @endphp

    <div
        class="min-h-screen relative flex flex-col items-center justify-start lg:justify-center p-0 lg:p-6 animated-gradient-bg">
        @if ($bgUrl)
            <div class="absolute inset-0 bg-cover bg-center mix-blend-overlay opacity-20"
                style="background-image: url('{{ $bgUrl }}');"></div>
        @endif

        <div class="w-full relative z-10 pt-10 pb-20 px-6 flex flex-col items-center text-center lg:hidden">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo"
                    class="w-20 h-20 object-contain bg-white/10 backdrop-blur-md rounded-full p-2 mb-4 shadow-lg border border-white/20">
            @endif
            <h1 class="text-2xl font-bold text-white tracking-tight">{{ $pengaturan->nama_aplikasi ?? 'Buku Tamu' }}</h1>
            <h2 class="text-xs font-medium text-white/80 uppercase tracking-widest mt-1">
                {{ $pengaturan->nama_instansi ?? 'Digital' }}</h2>
        </div>

        <div x-data="{ step: 1 }" class="relative z-20 w-full max-w-5xl -mt-10 lg:mt-0 px-4 lg:px-0 mb-10">
            <div
                class="bg-white rounded-3xl sm:rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-white/40 backdrop-blur-xl">

                <div class="hidden lg:flex lg:w-5/12 p-12 flex-col justify-center items-center text-center relative overflow-hidden"
                    style="background-color: {{ $warna }};">
                    <div class="absolute top-0 left-0 w-full h-full bg-black/10"></div>
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo"
                            class="relative z-10 w-32 h-32 object-contain bg-white rounded-full p-3 mb-8 shadow-2xl border-4 border-white/20">
                    @endif
                    <h1 class="relative z-10 text-3xl font-extrabold text-white mb-2">
                        {{ $pengaturan->nama_aplikasi ?? 'Buku Tamu' }}</h1>
                    <h2 class="relative z-10 text-sm font-semibold text-white/90 uppercase tracking-widest mb-6">
                        {{ $pengaturan->nama_instansi ?? 'Digital' }}</h2>
                    <p class="relative z-10 text-sm text-white/80 leading-relaxed max-w-xs">
                        {{ $pengaturan->pesan_sambutan ?? 'Selamat Datang! Ikuti langkah-langkah di samping untuk mengisi data kunjungan Anda.' }}
                    </p>
                </div>

                <div class="w-full lg:w-7/12 p-6 sm:p-10 lg:p-12 flex flex-col">

                    @if (!$buka_form)
                        <!-- TAMPILAN JIKA DILUAR JAM KERJA -->
                        <div class="flex-grow flex flex-col items-center justify-center text-center p-8">
                            <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Layanan Ditutup</h3>
                            <p class="text-gray-500">{{ $pesan_tutup }}</p>
                        </div>
                    @else
                        <!-- REVISI UI: Indikator Step dengan Garis Flexbox Sempurna (Tidak Bablas & Center) -->
                        <div class="flex items-center justify-between mb-8 px-2 sm:px-6">
                            <!-- Lingkaran 1 -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300 text-white"
                                    style="background-color: var(--warna-utama);">1</div>
                                <span
                                    class="text-[10px] font-bold mt-2 text-gray-600 uppercase absolute -bottom-5 w-20 text-center">Data
                                    Diri</span>
                            </div>

                            <!-- Garis 1 -> 2 -->
                            <div class="flex-1 h-1 transition-colors duration-500 mx-2"
                                :style="step >= 2 ? 'background-color: var(--warna-utama);' : 'background-color: #e5e7eb;'">
                            </div>

                            <!-- Lingkaran 2 -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300"
                                    :class="step >= 2 ? 'text-white' : 'bg-gray-200 text-gray-400'"
                                    :style="step >= 2 ? 'background-color: var(--warna-utama);' : ''">2</div>
                                <span
                                    class="text-[10px] font-bold mt-2 text-gray-600 uppercase absolute -bottom-5 w-20 text-center">Keperluan</span>
                            </div>

                            <!-- Garis 2 -> 3 -->
                            <div class="flex-1 h-1 transition-colors duration-500 mx-2"
                                :style="step >= 3 ? 'background-color: var(--warna-utama);' : 'background-color: #e5e7eb;'">
                            </div>

                            <!-- Lingkaran 3 -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300"
                                    :class="step >= 3 ? 'text-white' : 'bg-gray-200 text-gray-400'"
                                    :style="step >= 3 ? 'background-color: var(--warna-utama);' : ''">3</div>
                                <span
                                    class="text-[10px] font-bold mt-2 text-gray-600 uppercase absolute -bottom-5 w-20 text-center">Verifikasi</span>
                            </div>
                        </div>

                        <!-- Notifikasi Anti-Spam (Jika ada error) -->
                        @if (session()->has('error'))
                            <div
                                class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-sm font-medium flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="simpanData"
                            class="flex-grow flex flex-col justify-between mt-6 min-h-[350px]">

                            <!-- STEP 1: DATA DIRI -->
                            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-x-8"
                                x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="form-label">Nama <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="nama" required class="input-modern"
                                            placeholder="Budi Santoso">
                                    </div>
                                    <div>
                                        <label class="form-label">Asal Instansi</label>
                                        <input type="text" wire:model="asal_instansi" class="input-modern"
                                            placeholder="PT. Maju Jaya">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="form-label">No. HP / WA <span
                                                class="text-red-500">*</span></label>
                                        <input type="number" wire:model="no_hp" required class="input-modern"
                                            placeholder="08123456789">
                                    </div>
                                    <div>
                                        <label class="form-label">Alamat Lengkap <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="alamat" required class="input-modern"
                                            placeholder="Jl. Merdeka No. 10">
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: KEPERLUAN -->
                            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-x-8"
                                x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5"
                                style="display: none;">
                                <div>
                                    <label class="form-label">Kategori Keperluan <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model.live="kategori_keperluan" class="input-modern cursor-pointer">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Dinas / Kedinasan">Dinas / Kedinasan</option>
                                        <option value="Orang Tua / Wali Murid">Orang Tua / Wali Murid</option>
                                        <option value="Menemui Guru / Pegawai / Kepsek">Menemui Guru / Pegawai / Kepsek
                                        </option>
                                        <option value="Administrasi / Tata Usaha">Administrasi / Tata Usaha</option>
                                        <option value="Studi Banding / Kerja Sama">Studi Banding / Kerja Sama</option>
                                        <option value="Vendor / Sosialisasi">Vendor / Sosialisasi</option>
                                        <option value="Pengantaran Barang">Pengantaran Barang</option>
                                        <option value="Servis / Maintenance">Servis / Maintenance</option>
                                        <option value="Alumni">Alumni</option>
                                        <option value="Kegiatan Sekolah">Kegiatan Sekolah</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                @if ($kategori_keperluan === 'Menemui Guru / Pegawai / Kepsek')
                                    <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100">
                                        <label class="form-label text-blue-800">Menemui Siapa? <span
                                                class="text-red-500">*</span></label>
                                        <select wire:model="pegawai_id" class="input-modern border-blue-200">
                                            <option value="">-- Pilih Pegawai / Guru --</option>
                                            @foreach ($daftarPegawai as $pegawai)
                                                <option value="{{ $pegawai->id }}">{{ $pegawai->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="form-label">Detail Keperluan <span
                                            class="text-red-500">*</span></label>
                                    <textarea wire:model="detail_keperluan" rows="3" class="input-modern resize-none"
                                        placeholder="Tuliskan keterangan detail kedatangan Anda..."></textarea>
                                </div>
                            </div>

                            <!-- STEP 3: VERIFIKASI -->
                            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-x-8"
                                x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5"
                                style="display: none;">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="form-label text-center sm:text-left">Foto Selfie Tamu <span
                                                class="text-red-500">*</span></label>
                                        <div
                                            class="mt-1 border-2 border-dashed border-gray-300 rounded-2xl h-48 flex flex-col items-center justify-center bg-gray-50">
                                            <svg class="w-10 h-10 text-gray-400 mb-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                                </path>
                                            </svg>
                                            <span class="text-xs font-semibold text-gray-500">Kamera Area</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label text-center sm:text-left">Tanda Tangan <span
                                                class="text-red-500">*</span></label>
                                        <div
                                            class="mt-1 border-2 border-dashed border-gray-300 rounded-2xl h-48 flex flex-col items-center justify-center bg-gray-50">
                                            <svg class="w-10 h-10 text-gray-400 mb-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                </path>
                                            </svg>
                                            <span class="text-xs font-semibold text-gray-500">TTD Area</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TOMBOL NAVIGASI WIZARD (MENGGUNAKAN ICONS) -->
                            <div class="mt-8 pt-6 border-t flex justify-between items-center gap-4">
                                <!-- Tombol Kembali (Dengan Icon Arrow Left) -->
                                <button type="button" x-show="step > 1" @click="step--"
                                    class="flex items-center px-5 py-3 rounded-xl font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Kembali
                                </button>
                                <div x-show="step === 1"></div>

                                <!-- Tombol Lanjut (Dengan Icon Arrow Right) -->
                                <button type="button" x-show="step < 3" @click="step++"
                                    class="flex items-center px-6 py-3 rounded-xl font-bold text-white shadow-lg transition-transform transform active:scale-95"
                                    :style="'background-color: var(--warna-utama)'">
                                    Lanjut
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>

                                <!-- Tombol Simpan (Dengan Icon Paper Airplane/Send) -->
                                <button type="submit" x-show="step === 3" style="display: none;"
                                    class="flex items-center px-6 py-3 rounded-xl font-bold text-white shadow-lg bg-green-500 hover:bg-green-600 transition-transform transform active:scale-95">
                                    KIRIM DATA
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
