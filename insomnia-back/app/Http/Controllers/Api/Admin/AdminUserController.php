<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    // READ: Menampilkan semua user
    public function index(): JsonResponse
    {
        // Paginate agar server tidak crash jika user mencapai ribuan
        $users = User::paginate(10); 
        return response()->json(['success' => true, 'data' => $users]);
    }

    // CREATE: Admin membuat user baru secara manual
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['admin', 'pengguna'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true, 
            'message' => 'User berhasil dibuat', 
            'data' => $user
        ], 201);
    }

    // UPDATE: Admin mengubah data/role user lain
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);

        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => ['sometimes', Rule::in(['admin', 'pengguna'])],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'User berhasil diupdate', 'data' => $user]);
    }

    // DELETE: Admin menghapus akun user
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User berhasil dihapus']);
    }
}