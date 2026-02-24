<?php

namespace App\Extensions;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Support\Arrayable;

class SmartUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     * Override method bawaan Laravel untuk memilih tabel.
     */
    public function retrieveById($identifier): (\Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable)|null
    {
        // Cek tipe akun di session
        $type = session('auth_type');
        if ($type === 'mahasiswa') {
            $this->setModel(\App\Models\UserMahasiswa::class);
            return parent::retrieveById($identifier);
        }
        if ($type === 'dosen') {
            $this->setModel(\App\Models\UserDosenTendik::class);
            return parent::retrieveById($identifier);
        }

        $dosen = \App\Models\UserDosenTendik::query()->find($identifier);
        if ($dosen) {
            $this->setModel(\App\Models\UserDosenTendik::class);
            return $dosen;
        }

        $mahasiswa = \App\Models\UserMahasiswa::query()->find($identifier);
        if ($mahasiswa) {
            $this->setModel(\App\Models\UserMahasiswa::class);
            return $mahasiswa;
        }

        return null;
//        return parent::retrieveById($identifier);
    }
}
