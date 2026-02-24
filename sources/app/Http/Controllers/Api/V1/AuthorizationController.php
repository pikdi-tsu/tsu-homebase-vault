<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    public function getUserPermissions($id)
    {
        // Cari user di kedua tabel
        $user = UserDosenTendik::query()->where('username', $id) ?? UserMahasiswa::query()->where('username', $id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Ambil semua permission dan kembalikan hanya namanya
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json($permissions);
    }
}
