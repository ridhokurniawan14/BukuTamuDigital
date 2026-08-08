<?php

namespace App\Filament\Widgets;

use App\Models\Tamu;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TamuStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $hariIni = Tamu::whereDate('created_at', today())->count();
        $bulanIni = Tamu::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $belumPulang = Tamu::whereNull('waktu_keluar')->count();
        $lsmHariIni = Tamu::whereDate('created_at', today())->where('is_lsm', true)->count();

        return [
            Stat::make('Tamu Hari Ini', $hariIni)
                ->description('Total kunjungan hari ini')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Tamu Bulan Ini', $bulanIni)
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Belum Pulang', $belumPulang)
                ->description('Masih berada di lokasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($belumPulang > 0 ? 'warning' : 'success'),

            Stat::make('LSM Hari Ini', $lsmHariIni)
                ->description('Tamu ditandai LSM')
                ->descriptionIcon('heroicon-m-flag')
                ->color('danger'),
        ];
    }
}
