<?php

namespace App\Filament\Widgets;

use App\Models\Tamu;
use Filament\Widgets\ChartWidget;

class TamuPerJamWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected ?string $heading = 'Jam Paling Sering Dikunjungi';

    protected function getData(): array
    {
        $totalPerJam = Tamu::selectRaw('HOUR(created_at) as jam, COUNT(*) as total')
            ->groupBy('jam')
            ->pluck('total', 'jam');

        $data = collect(range(0, 23))->map(fn($i) => $totalPerJam[$i] ?? 0);
        $labels = collect(range(0, 23))->map(fn($i) => sprintf('%02d:00', $i));

        return [
            'datasets' => [[
                'label' => 'Jumlah Kunjungan',
                'data' => $data->toArray(),
                'backgroundColor' => '#8b5cf6',
            ]],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
