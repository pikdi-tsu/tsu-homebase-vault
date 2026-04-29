<?php

namespace Database\Seeders;

use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use App\Services\DefaultPasswordService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loggedAdmin = UserDosenTendik::query()->where('email', 'bertha@tsu.ac.id')->value('username');
        $mahasiswa1 = UserMahasiswa::query()->firstOrCreate(
                [
                    'email' => 'mahasiswa1@tsu.ac.id',
                ],
                [
                'username' => '25100001',
                'name' => 'Mahasiswa Satu',
                'password' => resolve(DefaultPasswordService::class)->getDefaultHashedPassword(),
                'created_by' => $loggedAdmin,
            ]);

        $mahasiswa2 = UserMahasiswa::query()->firstOrCreate(
                [
                    'email' => 'mahasiswa2@tsu.ac.id',
                ],
                [
                'username' => '25100002',
                'name' => 'Mahasiswa Dua',
                'password' => resolve(DefaultPasswordService::class)->getDefaultHashedPassword(),
                'created_by' => $loggedAdmin,
            ]);

        $mahasiswa3 = UserMahasiswa::query()->firstOrCreate(
                    [
                        'email' => 'mahasiswa3@tsu.ac.id',
                    ],
                    [
                    'username' => '25100003',
                    'name' => 'Mahasiswa Tiga',
                    'password' => resolve(DefaultPasswordService::class)->getDefaultHashedPassword(),
                    'created_by' => $loggedAdmin,
                ]);

        $mahasiswaRole = Role::query()->where('name', 'mahasiswa')->first();
        $mahasiswa1->assignRole($mahasiswaRole);
        $mahasiswa2->assignRole($mahasiswaRole);
        $mahasiswa3->assignRole($mahasiswaRole);
    }
}
