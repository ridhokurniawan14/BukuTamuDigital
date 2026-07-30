<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Schema; // Tambahan untuk cek tabel
use Illuminate\Support\Facades\Storage; // Tambahan untuk URL file

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // ==================================================
        // 1. AMBIL DATA PENGATURAN (Anti-Crash)
        // ==================================================
        $pengaturan = null;
        try {
            if (Schema::hasTable('pengaturans')) {
                $pengaturan = \App\Models\Pengaturan::first();
            }
        } catch (\Exception $e) {
        }

        $namaAplikasi = $pengaturan->nama_aplikasi ?? 'Buku Tamu Digital';
        $warnaUtama = $pengaturan->warna_utama ?? '#f59e0b'; // Default Amber

        $logo = ($pengaturan && $pengaturan->logo_instansi)
            ? asset(Storage::url($pengaturan->logo_instansi))
            : null;

        $favicon = ($pengaturan && $pengaturan->favicon)
            ? asset(Storage::url($pengaturan->favicon))
            : null;

        $background = ($pengaturan && $pengaturan->gambar_background)
            ? asset(Storage::url($pengaturan->gambar_background))
            : null;

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()

            // ==================================================
            // 2. TERAPKAN DATA BRANDING KE PANEL ADMIN
            // ==================================================
            ->brandName($namaAplikasi)
            ->brandLogo($logo)
            ->brandLogoHeight('3rem') // Ukuran proporsional logo
            ->favicon($favicon)
            ->colors([
                // Mengubah warna bawaan menjadi warna pilihan klien
                'primary' => Color::hex($warnaUtama),
            ])
            // Menyuntikkan CSS khusus Halaman Login untuk menampilkan Background
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn(): string => $background ? '<style>.fi-simple-layout { background-image: url("' . $background . '") !important; background-size: cover !important; background-position: center !important; }</style>' : ''
            )
            // ==================================================

            ->navigationGroups([
                'Data Master',
                'Filament Shield',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->resources([
                // 
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
