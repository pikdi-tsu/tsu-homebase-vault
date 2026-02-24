<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        // Oauth security Check
        $secret = config('app.pikdi.key.sync');
        if ($request->header('X-Sync-Secret') !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $dosenQuery = UserDosenTendik::query()->where('isactive', 1);
        $mhsQuery   = UserMahasiswa::query()->where('isactive', 1);

        // Sync User Lokal Client
        if ($request->has('emails') && is_array($request->emails)) {
            $dosenQuery->whereIn('email', $request->emails);
            $mhsQuery->whereIn('email', $request->emails);
        }
        // User Baru (Import Manual)
        elseif ($request->has('q')) {
            $keyword = $request->q;

            $dosenQuery->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('username', 'like', "%$keyword%") // NIK
                    ->orWhere('email', 'like', "%$keyword%");
            });

            $mhsQuery->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('username', 'like', "%$keyword%") // NIM
                    ->orWhere('email', 'like', "%$keyword%");
            });

            // Limit biar gak berat pas searching
            $dosenQuery->limit(20);
            $mhsQuery->limit(20);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Harus kirim filter emails atau keyword pencarian (q).']);
        }

        $dosenResults = $dosenQuery->get();
        $mhsResults   = $mhsQuery->get();

        // Ambil Dosen Tendik
//        $listDosenTendik = UserDosenTendik::query()->where('isactive', 1)->get();
        $mappedDosen = $dosenResults->map(function ($row) {
            // Get roles user
            $userRoles = $row->roles->map(function($role) {
                return [
                    'name' => $role->name,
                    'is_identity' => (bool) $role->is_identity
                ];
            });

            return [
                'id'                => $row->id,
                'name'              => $row->name,
                'username'          => (string) $row->username,
                'email'             => $row->email,
                'profile_photo_url' => $row->profile_photo_path,
                'isactive'          => (bool) $row->isactive,
                'roles'             => $userRoles,
                'nidn'              => $row->nidn,
                'nik'               => $row->username,
                'unit'              => $row->unit,
            ];
        });

        // Ambil Mahasiswa
//        $listMhs = UserMahasiswa::query()->where('isactive', 1)->get();
        $mappedMhs = $mhsResults->map(function ($row) {
            // Get roles user
            $userRoles = $row->roles->map(function($role) {
                return [
                    'name' => $role->name,
                    'is_identity' => (bool) $role->is_identity
                ];
            });

            return [
                'id'                => $row->id,
                'name'              => $row->name,
                'username'          => (string) $row->username,
                'email'             => $row->email,
                'profile_photo_url' => $row->profile_photo_path,
                'isactive'          => (bool) $row->isactive,
                'roles'             => $userRoles,
                'nim'               => (string) $row->username,
                'unit'              => $row->unit,
            ];
        });

        // Merge Data User
        $finalData = $mappedDosen->merge($mappedMhs);

        return response()->json([
            'status' => 'success',
            'total'  => $finalData->count(),
            'data'   => $finalData->values()
        ]);
    }

    // Method untuk mendapatkan model yang benar berdasarkan request
    private function getUserModel(Request $request)
    {
        if ($request->input('user_type') === 'mahasiswa') {
            return new UserMahasiswa();
        }
        return new UserDosenTendik();
    }

    /**
     * GET /api/v1/users
     * Menampilkan daftar semua user.
     */
    public function index(Request $request)
    {
        $model = $this->getUserModel($request);
        $users = $model->paginate(15);

        return response()->json($users);
    }

    /**
     * POST /api/v1/users
     * Membuat user baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users_dosen_tendik|unique:users_mahasiswa',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:dosen_tendik,mahasiswa',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $model = $this->getUserModel($request);

        $user = $model->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    /**
     * GET /api/v1/users/{id}
     * Menampilkan satu user spesifik.
     * Catatan: Metode ini perlu logika tambahan untuk tahu harus mencari di tabel mana.
     */
    public function show(Request $request, $id)
    {
        // Di sini kita asumsikan ID unik di kedua tabel atau perlu parameter tambahan
        $user = UserDosenTendik::query()->where('username', $id) ?? UserMahasiswa::query()->where('username', $id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Coba cari user di kedua tabel
        $user = UserDosenTendik::query()->where('username', $id) ?? UserMahasiswa::query()->where('username', $id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            // 'sometimes' berarti validasi hanya jika field-nya ada di request
            // Kita juga perlu mengabaikan email user saat ini saat cek unique
            'email' => 'sometimes|string|email|max:255|unique:users_dosen_tendik,email,'.$id.'|unique:users_mahasiswa,email,'.$id,
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = UserDosenTendik::query()->where('username', $id) ?? UserMahasiswa::query()->where('username', $id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        // Kembalikan respons kosong dengan status 204 No Content
        return response()->json(null, 204);
    }
}
