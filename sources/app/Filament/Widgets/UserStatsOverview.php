<?php

namespace App\Filament\Widgets;

use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UserStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected int | null | array $columns = 2;

    protected int | string | array $columnSpan = 'full';

//    protected function getStats(): array
//    {
//        return [
//            Stat::make('Total Dosen & Tendik', UserDosenTendik::count())
//                ->icon('heroicon-o-academic-cap'),
//            Stat::make('Total Mahasiswa', UserMahasiswa::count()) // Ganti dengan model Mahasiswa-mu
//            ->icon('heroicon-o-users'),
//        ];
//    }

    protected function getStats(): array
    {
        // Ambil & cache data total dosen/tendik selama 10 menit
        $totalDosenTendik = Cache::remember('stats_total_dosen_tendik', now()->addMinutes(10), static function () {
            return \App\Models\UserDosenTendik::count();
        });

        // Ambil & cache data total mahasiswa selama 10 menit
        $totalMahasiswa = Cache::remember('stats_total_mahasiswa', now()->addMinutes(10), static function () {
            return \App\Models\UserMahasiswa::count();
        });

        return [
            // Gunakan variabel yang sudah di-cache
            Stat::make('Total Dosen & Tendik', $totalDosenTendik)
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Total Mahasiswa', $totalMahasiswa)
                ->icon('heroicon-o-users'),
        ];
    }
}
