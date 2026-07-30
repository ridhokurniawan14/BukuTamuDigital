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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString; // <-- JURUS BARU: Untuk render HTML Custom

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
        $warnaUtama = $pengaturan->warna_utama ?? '#f59e0b';

        $logo = ($pengaturan && $pengaturan->logo_instansi)
            ? asset(Storage::url($pengaturan->logo_instansi))
            : null;

        $favicon = ($pengaturan && $pengaturan->favicon)
            ? asset(Storage::url($pengaturan->favicon))
            : null;

        $background = ($pengaturan && $pengaturan->gambar_background)
            ? asset(Storage::url($pengaturan->gambar_background))
            : null;

        // ==================================================
        // JURUS SAKTI: Gabungkan Logo & Teks pakai HTML Flexbox
        // ==================================================
        $brandHtml = new HtmlString('
            <div style="display: flex; align-items: center; gap: 12px;">
                ' . ($logo ? '<img src="' . $logo . '" alt="Logo" style="height: 3rem; border-radius: 0.25rem;">' : '') . '
                <span style="font-size: 1.25rem; font-weight: bold; line-height: 1.2;">' . $namaAplikasi . '</span>
            </div>
        ');

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()

            // ==================================================
            // 2. TERAPKAN DATA BRANDING KE PANEL ADMIN
            // ==================================================
            ->brandName($namaAplikasi) // Fallback nama
            ->brandLogo($brandHtml) // Pakai gabungan logo & teks
            ->brandLogoHeight('3rem') // Sesuaikan dengan tinggi logo di HTML
            ->favicon($favicon)
            ->colors([
                'primary' => Color::hex($warnaUtama),
            ])
            // REVISI CSS: Tambahkan overlay hitam transparan (rgba) di atas gambar background
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn(): string => $background ? '<style>
                    .fi-simple-layout { 
                        background-image: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), url("' . $background . '") !important; 
                        background-size: cover !important; 
                        background-position: center !important; 
                    }
                </style>' : ''
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
