<?php

namespace App\Filament\Widgets;

use App\Models\Tamu;
use Filament\Widgets\ChartWidget;

class TamuPerHariWidget extends ChartWidget
{
    protected static ?int $sort = 4;
    protected ?string $heading = 'Hari Paling Sering Dikunjungi';

    protected function getData(): array
    {
        $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $totalPerHari = Tamu::selectRaw('WEEKDAY(created_at) as hari_ke, COUNT(*) as total')
            ->groupBy('hari_ke')
            ->pluck('total', 'hari_ke');
        // WEEKDAY(): 0=Senin ... 6=Minggu, pas urutannya sama kayak $namaHari

        $data = collect(range(0, 6))->map(fn($i) => $totalPerHari[$i] ?? 0);

        return [
            'datasets' => [[
                'label' => 'Jumlah Kunjungan',
                'data' => $data->toArray(),
                'backgroundColor' => '#3b82f6',
            ]],
            'labels' => $namaHari,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
