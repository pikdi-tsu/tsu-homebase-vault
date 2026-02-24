<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    /**
     * Menampilkan data profil user yang sedang login.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $user->load(['roles', 'permissions']);

        return response()->json($user);
    }

    /**
     * Memperbarui data profil user yang sedang login.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            // Pastikan email unik, tapi abaikan email user saat ini
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    /**
     * Mengubah password user yang sedang login.
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Password saat ini tidak cocok.');
                }
            }],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Password berhasil diubah.'], 200);
        }

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function updatePhoto(Request $request)
    {
        // Validasi File
        $validator = Validator::make($request->all(), [
            'photoprofile' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = $request->user(); // Ambil user dari Token

        // Proses Upload
        if ($request->hasFile('photoprofile')) {
            // Update Database
            $user->updateProfilePhoto($request->file('photoprofile'));

            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil berhasil diperbarui',
                'data' => [
                    // Ambil URL resmi dari Jetstream Accessor
                    'photo_url' => $user->profile_photo_url
                ]
            ]);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }
}
