<?php

namespace App\Health;

use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Symfony\Component\Process\Process;

class IndonesianWindowsDiskSpaceCheck extends UsedDiskSpaceCheck
{
    protected function getDiskUsagePercentage(): int
    {
        $process = Process::fromShellCommandline('wmic logicaldisk get Caption,FreeSpace,Size /Locale:MS_409');
        $process->run();

        // Sekarang kita panggil method parseWindowsOutput yang ada di kelas ini sendiri
        return $this->parseWindowsOutput($process->getOutput());
    }

    // SALIN SELURUH METHOD DI BAWAH INI KE DALAM KELAS ANDA
    protected function parseWindowsOutput(string $output): int
    {
        $disks = collect(preg_split('/(?<=\n)(?=[A-Z]:)/', trim($output)))
            ->reject(fn (string $diskOutput) => trim($diskOutput) === '') // 1. Abaikan baris kosong
            ->map(function (string $disk) {
                $lines = array_values(array_filter(explode("\n", $disk), 'trim')); // 2. Pecah dan bersihkan

                // 3. Pastikan kita punya data yang cukup sebelum memproses
                if (count($lines) < 3) {
                    return null;
                }

                return (object) [
                    'caption' => trim($lines[0]),
                    'free' => trim($lines[1]),
                    'size' => trim($lines[2]),
                ];
            })
            ->filter(); // 4. Buang hasil null yang tidak valid

        $totalSize = $disks->sum('size');
        $totalFree = $disks->sum('free');

        if ($totalSize === 0) {
            return 0;
        }

        return (int) round(100 - (($totalFree / $totalSize) * 100));
    }
}
