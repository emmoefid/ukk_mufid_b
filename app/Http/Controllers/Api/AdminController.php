<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // 1. Lihat semua user
    public function getAllUsers()
    {
        $users = User::all(['id', 'name', 'email', 'role', 'created_at']);
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    // 2. Tambah user baru
    public function addUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,manajer,kasir',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'tambah_user',
            'description' => 'Menambah user: ' . $user->name . ' (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    // 3. Edit role user
    public function updateUserRole(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'role' => 'required|in:admin,manajer,kasir',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'edit_user_role',
            'description' => 'Mengubah role user ' . $user->name . ' dari ' . $oldRole . ' menjadi ' . $request->role,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role user berhasil diupdate',
            'data' => $user
        ]);
    }

    // 4. Hapus user
    public function deleteUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // Cegah menghapus diri sendiri
        if ($user->id === $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 400);
        }

        $userName = $user->name;
        $user->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'hapus_user',
            'description' => 'Menghapus user: ' . $userName,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dihapus'
        ]);
    }

    // 5. Lihat semua log aktivitas
    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    // 6. Lihat detail satu user
    public function getUserDetail($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
}