<div>
    <!-- CSS Tailwind & Plugin External -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tambahan SweetAlert2 untuk Notifikasi Pop-up -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

    <!-- JS External (PENTING: Face-API, Signature Pad, TomSelect) -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

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
        }

        /* Modifikasi UI TomSelect Biar Ala Modern Input */
        .ts-control {
            background-color: #f8fafc;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border-color: #e2e8f0;
        }

        .ts-control.focus {
            box-shadow: 0 0 0 4px rgba(var(--warna-rgb), 0.15);
            border-color: var(--warna-utama);
        }

        /* Area Kamera Bulat ala Kiosk Masa Depan */
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 300px;
            aspect-ratio: 1;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 50%;
            border: 4px solid #e5e7eb;
            transition: border-color 0.3s;
        }

        .camera-container.face-detected {
            border-color: #22c55e;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
        }

        .camera-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
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
        </div>

        <div x-data="{ step: 1 }" class="relative z-20 w-full max-w-5xl -mt-10 lg:mt-0 px-4 lg:px-0 mb-10">
            <div
                class="bg-white rounded-3xl sm:rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-white/40 backdrop-blur-xl">

                <!-- KIRI -->
                <div class="hidden lg:flex lg:w-4/12 p-12 flex-col justify-center items-center text-center relative overflow-hidden"
                    style="background-color: {{ $warna }};">
                    <div class="absolute top-0 left-0 w-full h-full bg-black/10"></div>
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo"
                            class="relative z-10 w-32 h-32 object-contain bg-white rounded-full p-3 mb-8 shadow-2xl border-4 border-white/20">
                    @endif
                    <h1 class="relative z-10 text-3xl font-extrabold text-white mb-2">
                        {{ $pengaturan->nama_aplikasi ?? 'Buku Tamu' }}</h1>
                    <p class="relative z-10 text-sm text-white/80 leading-relaxed max-w-xs mt-4">
                        Ikuti langkah di samping untuk mengisi data. Sistem dilengkapi dengan pendeteksi wajah dan
                        validasi cerdas.
                    </p>
                </div>

                <!-- KANAN (Area Form) -->
                <div class="w-full lg:w-8/12 p-6 sm:p-10 lg:p-12 flex flex-col">
                    @if (!$buka_form)
                        <div class="flex-grow flex flex-col items-center justify-center text-center p-8">
                            <h3 class="text-2xl font-bold text-red-600 mb-2">Layanan Ditutup</h3>
                            <p class="text-gray-500 font-medium">{{ $pesan_tutup }}</p>
                        </div>
                    @else
                        <!-- Indikator Step Flexbox Centered -->
                        <div class="flex items-center justify-between mb-8 px-2 sm:px-6">
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm text-white transition-colors"
                                    style="background-color: var(--warna-utama);">1</div>
                                <span
                                    class="text-[10px] font-bold mt-2 text-gray-600 uppercase absolute -bottom-5 w-24 text-center">Data
                                    Diri</span>
                            </div>
                            <div class="flex-1 h-1 transition-colors mx-2"
                                :style="step >= 2 ? 'background-color: var(--warna-utama);' : 'background-color: #e5e7eb;'">
                            </div>
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors"
                                    :class="step >= 2 ? 'text-white' : 'bg-gray-200 text-gray-400'"
                                    :style="step >= 2 ? 'background-color: var(--warna-utama);' : ''">2</div>
                                <span
                                    class="text-[10px] font-bold mt-2 text-gray-600 uppercase absolute -bottom-5 w-24 text-center">Keperluan</span>
                            </div>
                            <div class="flex-1 h-1 transition-colors mx-2"
                                :style="step >= 3 ? 'background-color: var(--warna-utama);' : 'background-color: #e5e7eb;'">
                            </div>
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors"
                                    :class="step >= 3 ? 'text-white' : 'bg-gray-200 text-gray-400'"
                                    :style="step >= 3 ? 'background-color: var(--warna-utama);' : ''">3</div>
                                <span
                                    class="text-[10px] font-bold mt-2 text-gray-600 uppercase absolute -bottom-5 w-24 text-center">Verifikasi</span>
                            </div>
                        </div>

                        <!-- Notifikasi Validasi & Error -->
                        @if ($errors->any() || session()->has('error') || session()->has('success'))
                            <div
                                class="mb-4 text-sm font-medium p-4 rounded-xl {{ session()->has('success') ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-600 border border-red-200' }}">
                                @if (session()->has('success'))
                                    {{ session('success') }}
                                @elseif(session()->has('error'))
                                    {{ session('error') }}
                                @else
                                    Data belum lengkap! Harap periksa kembali isian Anda.
                                @endif
                            </div>
                        @endif

                        <form wire:submit.prevent="simpanData"
                            class="flex-grow flex flex-col justify-between mt-6 min-h-[400px]">

                            <!-- STEP 1: DATA DIRI -->
                            <div x-show="step === 1" class="step-container space-y-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="form-label">Nama <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="nama" required class="input-modern"
                                            placeholder="Budi Santoso">
                                        @error('nama')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
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
                                        @error('no_hp')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="form-label">Alamat Lengkap <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="alamat" required class="input-modern"
                                            placeholder="Jl. Merdeka No. 10">
                                        @error('alamat')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: KEPERLUAN -->
                            <div x-show="step === 2" style="display: none;" class="step-container space-y-5">
                                <!-- DROPDOWN PENCARIAN (TOMSELECT) -->
                                <div wire:ignore>
                                    <label class="form-label">Kategori Keperluan <span
                                            class="text-red-500">*</span></label>
                                    <select required id="kategori-select" x-init="new TomSelect($el, { create: false, onChange: function(v) { $wire.set('kategori_keperluan', v); } })"
                                        placeholder="Cari atau pilih kategori...">
                                        <option value="">-- Ketik untuk mencari kategori --</option>
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
                                @error('kategori_keperluan')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror

                                @if ($kategori_keperluan === 'Menemui Guru / Pegawai / Kepsek')
                                    <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100" wire:ignore>
                                        <label class="form-label text-blue-800">Cari Nama Pegawai/Guru <span
                                                class="text-red-500">*</span></label>
                                        <select required id="pegawai-select" x-init="new TomSelect($el, { create: false, onChange: function(v) { $wire.set('pegawai_id', v); } })"
                                            placeholder="Ketik nama untuk mencari...">
                                            <option value="">-- Ketik nama pegawai --</option>
                                            @foreach ($daftarPegawai as $pegawai)
                                                <option value="{{ $pegawai->id }}">{{ $pegawai->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="form-label">Detail Keperluan <span
                                            class="text-red-500">*</span></label>
                                    <textarea wire:model="detail_keperluan" required rows="3" class="input-modern resize-none"
                                        placeholder="Tuliskan keterangan detail kedatangan Anda..."></textarea>
                                    @error('detail_keperluan')
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- STEP 3: VERIFIKASI (KAMERA AI & TTD) -->
                            <div x-show="step === 3" style="display: none;" class="step-container space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- MODULE 1: KAMERA AI FACE DETECTION -->
                                    <div x-data="cameraAiComponent()" @start-camera.window="initAI()"
                                        class="flex flex-col items-center">
                                        <label class="form-label text-center mb-2">Verifikasi Wajah AI <span
                                                class="text-red-500">*</span></label>

                                        <!-- Tampilan Kamera / Hasil -->
                                        <div class="camera-container"
                                            :class="{
                                                'face-detected': isFaceDetected && !isCaptured,
                                                'border-red-500': !isFaceDetected && !isCaptured
                                            }">
                                            <video x-ref="video" autoplay muted playsinline class="camera-video"
                                                x-show="!isCaptured"></video>
                                            <canvas x-ref="canvas" style="display:none;"></canvas>
                                            <img :src="photoData" x-show="isCaptured"
                                                class="w-full h-full object-cover rounded-full">

                                            <!-- Overlay Status Wajah -->
                                            <div x-show="!isCaptured && !isFaceDetected && isLoading"
                                                class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center text-white text-xs font-bold text-center p-4">
                                                <svg class="animate-spin h-6 w-6 mb-2 text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Loading AI...
                                            </div>
                                            <div x-show="!isCaptured && !isFaceDetected && !isLoading"
                                                class="absolute inset-0 bg-black/30 flex items-center justify-center text-white text-xs font-bold text-center">
                                                <span class="bg-red-500 px-3 py-1 rounded-full">Wajah Tidak
                                                    Terdeteksi</span>
                                            </div>
                                            <div x-show="!isCaptured && isFaceDetected"
                                                class="absolute bottom-4 left-0 w-full flex justify-center">
                                                <span
                                                    class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">Wajah
                                                    Sesuai</span>
                                            </div>
                                        </div>

                                        <!-- Tombol Capture -->
                                        <div class="mt-4 flex gap-2">
                                            <button type="button" @click="capturePhoto()" x-show="!isCaptured"
                                                :disabled="!isFaceDetected"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-md disabled:bg-gray-400 transition-colors">
                                                Ambil Foto
                                            </button>
                                            <button type="button" @click="retakePhoto()" x-show="isCaptured"
                                                class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-bold shadow-md hover:bg-gray-600 transition-colors">
                                                Ulangi Foto
                                            </button>
                                        </div>
                                        @error('foto_selfie')
                                            <span class="text-xs text-red-500 mt-2">{{ $message }}</span>
                                        @enderror
                                        <input type="hidden" wire:model="foto_selfie">
                                    </div>

                                    <!-- MODULE 2: SIGNATURE PAD -->
                                    <div x-data="signatureComponent()" x-init="initSignature()"
                                        class="flex flex-col items-center">
                                        <label class="form-label text-center mb-2">Tanda Tangan Digital <span
                                                class="text-red-500">*</span></label>
                                        <div class="w-full max-w-[300px] bg-white border-2 border-dashed border-gray-300 rounded-2xl overflow-hidden touch-none"
                                            @mouseup="saveSignature()" @touchend="saveSignature()">
                                            <canvas x-ref="sigCanvas" class="w-full h-48 cursor-crosshair"></canvas>
                                        </div>
                                        <button type="button" @click="clearSignature()"
                                            class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-lg text-sm font-bold shadow-md transition-colors">
                                            Hapus TTD
                                        </button>
                                        @error('tanda_tangan')
                                            <span class="text-xs text-red-500 mt-2">{{ $message }}</span>
                                        @enderror
                                        <input type="hidden" wire:model="tanda_tangan">
                                    </div>
                                </div>
                            </div>

                            <!-- TOMBOL NAVIGASI DENGAN VALIDASI PINTAR (JURUS JITU) -->
                            <div class="mt-8 pt-6 border-t flex justify-between items-center gap-4">
                                <button type="button" x-show="step > 1" @click="step--"
                                    class="flex items-center px-4 py-2 sm:px-5 sm:py-3 rounded-xl font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Kembali
                                </button>
                                <div x-show="step === 1"></div>

                                <!-- Tombol Lanjut (Dengan Validasi & SweetAlert2) -->
                                <button type="button" x-show="step < 3"
                                    @click="
                                        let valid = true;
                                        let stepContainer = $el.closest('.step-container');
                                        
                                        stepContainer.querySelectorAll('input[required], textarea[required], select[required]').forEach(el => {
                                            if(!el.checkValidity()) { 
                                                valid = false;
                                                el.classList.add('border-red-500', 'bg-red-50'); // Beri efek merah
                                            } else {
                                                el.classList.remove('border-red-500', 'bg-red-50');
                                            }
                                        });

                                        if(valid) { 
                                            step++; 
                                            if(step === 3) { $dispatch('start-camera'); }
                                        } else {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Tunggu Dulu!',
                                                text: 'Masih ada form wajib yang belum Anda lengkapi.',
                                                confirmButtonColor: 'var(--warna-utama)',
                                                confirmButtonText: 'Baik, Saya Lengkapi'
                                            });
                                        }
                                    "
                                    class="flex items-center px-6 py-2 sm:px-8 sm:py-3 rounded-xl font-bold text-white shadow-lg transition-transform transform active:scale-95"
                                    :style="'background-color: var(--warna-utama)'">
                                    Lanjut
                                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>

                                <button type="submit" x-show="step === 3" style="display: none;"
                                    class="flex items-center px-6 py-2 sm:px-8 sm:py-3 rounded-xl font-bold text-white shadow-lg bg-green-500 hover:bg-green-600 transition-transform transform active:scale-95">
                                    KIRIM DATA
                                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor"
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

    <!-- SCRIPT ALPINE UNTUK KAMERA AI & TANDA TANGAN -->
    <script>
        function cameraAiComponent() {
            return {
                video: null,
                stream: null,
                isFaceDetected: false,
                isCaptured: false,
                isLoading: true,
                photoData: '',
                async initAI() {
                    this.video = this.$refs.video;
                    // Mengambil Model AI Face-API langsung dari CDN
                    const MODEL_URL = 'https://cdn.jsdelivr.net/gh/cgarciagl/face-api.js@master/weights';
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                    this.isLoading = false;
                    this.startCamera();
                },
                async startCamera() {
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'user'
                            }
                        });
                        this.video.srcObject = this.stream;

                        // Looping Deteksi Wajah setiap 300ms
                        setInterval(async () => {
                            if (!this.isCaptured && !this.isLoading) {
                                const detections = await faceapi.detectAllFaces(this.video, new faceapi
                                    .TinyFaceDetectorOptions());
                                // Jika array tidak kosong, berarti ada wajah
                                this.isFaceDetected = detections.length > 0;
                            }
                        }, 300);
                    } catch (err) {
                        alert("Tidak dapat mengakses kamera! Pastikan izin kamera diberikan.");
                    }
                },
                capturePhoto() {
                    if (!this.isFaceDetected) return; // Kunci ketat: tidak bisa klik kalau gak ada wajah
                    const canvas = this.$refs.canvas;
                    canvas.width = this.video.videoWidth;
                    canvas.height = this.video.videoHeight;
                    // Mirror efek untuk hasil canvas
                    const ctx = canvas.getContext('2d');
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(this.video, 0, 0, canvas.width, canvas.height);

                    this.photoData = canvas.toDataURL('image/jpeg', 0.8);
                    @this.set('foto_selfie', this.photoData); // Kirim base64 ke Livewire PHP
                    this.isCaptured = true;
                },
                retakePhoto() {
                    this.isCaptured = false;
                    this.photoData = '';
                    @this.set('foto_selfie', null);
                }
            }
        }

        function signatureComponent() {
            return {
                pad: null,
                initSignature() {
                    const canvas = this.$refs.sigCanvas;
                    // Menyesuaikan ukuran canvas agar tidak pecah
                    canvas.width = canvas.offsetWidth;
                    canvas.height = canvas.offsetHeight;
                    this.pad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)'
                    });
                },
                clearSignature() {
                    this.pad.clear();
                    @this.set('tanda_tangan', null);
                },
                saveSignature() {
                    if (!this.pad.isEmpty()) {
                        @this.set('tanda_tangan', this.pad.toDataURL('image/png'));
                    }
                }
            }
        }
    </script>
</div>
