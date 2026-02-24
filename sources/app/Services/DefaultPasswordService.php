<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class DefaultPasswordService
{
    /**
     * Mengambil password default dari config dan melakukan hashing.
     */
    public function getDefaultHashedPassword(): string
    {
        $defaultPassword = config('auth.defaults.default_password');

        return Hash::make($defaultPassword);
    }
}
