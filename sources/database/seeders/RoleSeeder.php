<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'dosen', 'guard_name' => 'api']);
        Role::query()->firstOrCreate(['name' => 'tendik', 'guard_name' => 'api']);
        Role::query()->firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'api2']);
//        Role::firstOrCreate(['name' => 'admin']);
//        Role::firstOrCreate(['name' => 'dosen']);
//        Role::firstOrCreate(['name' => 'tendik']);
//        Role::firstOrCreate(['name' => 'mahasiswa']);
    }
}
