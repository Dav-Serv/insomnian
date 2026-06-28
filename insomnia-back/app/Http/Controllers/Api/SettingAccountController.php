<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingAccountController extends Controller
{
    public function index(Request $request){
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profil pengguna berhasil dimuat',
            'data' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null, 
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi inputan (Password bersifat opsional, jika diisi baru di-update)
        $request->validate([
            'name'     => 'required|string|max:255',
            'password' => ['nullable', 'string', Password::min(8)], // Minimal 8 karakter jika ingin ganti password
            'photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Maksimal 2MB
        ]);

        // Masukkan data nama yang wajib diubah
        $data = [
            'name' => $request->name,
        ];

        // Jika user mengisi kolom password baru
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Jika ada file foto yang diunggah
        if ($request->hasFile('photo')) {
            // Hapus foto lama dari storage jika ada
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Simpan foto baru ke folder 'profile_photos' di disk public
            $path = $request->file('photo')->store('users', 'public');
            $data['photo'] = $path;
        }

        // Update data ke database
        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil akun berhasil diperbarui',
            'data' => [
                'name'  => $user->name,
                'email' => $user->email, // Email tetap dikembalikan tapi tidak di-update
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
        ]);
    }
}
