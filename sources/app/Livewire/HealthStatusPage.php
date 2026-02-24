<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\ResultStores;

class HealthStatusPage extends Component
{
    public function render()
    {
        // 1. Dapatkan UUID dari batch pemeriksaan terakhir
        $latestBatch = HealthCheckResultHistoryItem::query()->latest()->value('batch');

        // 2. Dapatkan semua hasil pemeriksaan dari batch terakhir tersebut
        $latestChecks = HealthCheckResultHistoryItem::query()->where('batch', $latestBatch)->get();

        // 3. Kirim koleksi data langsung ke view
        return view('livewire.health-status-page', [
            'checkResults' => $latestChecks,
        ])->layout('layouts.app');
    }
}
