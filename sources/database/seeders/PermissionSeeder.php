<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions untuk Homebase Core
        Permission::create(['name' => 'homebase:oauth-client:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:oauth-client:create', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:oauth-client:edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:oauth-client:delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'homebase:module:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:module:create', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:module:edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:module:delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'homebase:role:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:role:create', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:role:edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:role:delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'homebase:permission:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:permission:create', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:permission:edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:permission:delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'homebase:pertanyaan-keamanan:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:pertanyaan-keamanan:crate', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:pertanyaan-keamanan:edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:pertanyaan-keamanan:delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'homebase:user-dosen-tendik:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:user-dosen-tendik:create', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:user-dosen-tendik:update', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:user-dosen-tendik:delete', 'guard_name' => 'web']);

        Permission::create(['name' => 'homebase:user-mahasiswa:view', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:user-mahasiswa:create', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:user-mahasiswa:update', 'guard_name' => 'web']);
        Permission::create(['name' => 'homebase:user-mahasiswa:delete', 'guard_name' => 'web']);

        Role::query()->where('name', 'super admin')->first();
        $adminRole = Role::query()->where('name', 'admin')->first();
        $adminRole->givePermissionTo([
            'homebase:oauth-client:view',
            'homebase:module:view',
            'homebase:role:view',
            'homebase:permission:view',
            'homebase:pertanyaan-keamanan:view',
            'homebase:user-dosen-tendik:view',
            'homebase:user-mahasiswa:view'
        ]);

//        // Buat Role Dosen
//        $dosenRole = Role::query()->where('name', 'dosen')->first();
//        $dosenRole->givePermissionTo([
//            'pmb:user:view-any',
//        ]);
//
//        // Buat Role Mahasiswa
//        $mahasiswaRole = Role::query()->where('name', 'mahasiswa')->first();
//        $mahasiswaRole->givePermissionTo('akademik:jadwal:view');
    }
}
