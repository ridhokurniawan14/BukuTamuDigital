<?php

namespace App\Filament\Widgets;

use App\Models\Tamu;
use Filament\Widgets\ChartWidget;

class TamuChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'Kunjungan 7 Hari Terakhir (Umum vs LSM)';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = collect(range(6, 0))->map(function ($i) {
            $tanggal = now()->subDays($i);
            return [
                'label' => $tanggal->translatedFormat('D, d M'),
                'umum' => Tamu::whereDate('created_at', $tanggal)->where('is_lsm', false)->count(),
                'lsm' => Tamu::whereDate('created_at', $tanggal)->where('is_lsm', true)->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Tamu Umum',
                    'data' => $data->pluck('umum')->toArray(),
                    'backgroundColor' => '#f59e0b',
                ],
                [
                    'label' => 'Tamu LSM',
                    'data' => $data->pluck('lsm')->toArray(),
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }
    protected function getType(): string
    {
        return 'bar';
    }
    protected function getMaxHeight(): ?string
    {
        return '260px';
    }
}
