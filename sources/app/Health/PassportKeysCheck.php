<?php

namespace App\Health;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class PassportKeysCheck extends Check
{
    // Kasih nama sensor biar keren di dashboard
    protected ?string $label = 'Passport OAuth Keys';

    public function run(): Result
    {
        $privateKey = storage_path('oauth-private.key');
        $publicKey = storage_path('oauth-public.key');

        // Cek apakah kedua file kunci ada
        if (file_exists($privateKey) && file_exists($publicKey)) {
            return Result::make()
                ->ok()
                ->shortSummary('Keys Available')
                ->meta(['private' => 'Found', 'public' => 'Found']);
        }

        // Kalau salah satu hilang, bunyikan alarm!
        return Result::make()
            ->failed()
            ->shortSummary('Keys Missing!')
            ->meta([
                'private_exists' => file_exists($privateKey),
                'public_exists' => file_exists($publicKey),
            ])
            ->notificationMessage('WARNING! OAuth Key Passport File missing from storage server!');
    }
}
