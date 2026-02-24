<?php

namespace App\Filament\Widgets;

use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class UserCompositionChart extends ChartWidget
{
    protected ?string $heading = 'Komposisi Pengguna';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected static ?int $sort = 0;

    protected function getData(): array
    {
        // Simpan hasil data chart di cache selama 15 menit
        return Cache::remember('chart_user_composition', now()->addMinutes(15), static function () {
            $dosenTendikCount = UserDosenTendik::query()->count();
            $mahasiswaCount = UserMahasiswa::query()->count();

            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Pengguna',
                        'data' => [$dosenTendikCount, $mahasiswaCount],
                        'backgroundColor' => ['rgb(26, 130, 143)', 'rgb(245, 185, 71)'],
                        'hoverBackgroundColor' => ['rgb(22, 111, 122)', 'rgb(221, 166, 64)'],
                    ],
                ],
                'labels' => ['Dosen & Tendik', 'Mahasiswa'],
            ];
        });
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'align' => 'center',
            ],
            'maintainAspectRatio' => false,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
