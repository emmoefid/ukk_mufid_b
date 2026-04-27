<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManajerController extends Controller
{
    // ============ MANAJEMEN MENU ============

    // 1. Lihat semua menu
    public function getMenu()
    {
        $menus = Menu::all();
        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    // 2. Tambah menu baru
    public function addMenu(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'price' => $request->price,
            'category' => $request->category ?? 'Umum',
            'stock' => $request->stock ?? 0,
        ]);

        // Log aktivitas
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'tambah_menu',
            'description' => 'Menambah menu: ' . $menu->name,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu berhasil ditambahkan',
            'data' => $menu
        ], 201);
    }

    // 3. Edit menu
    public function updateMenu(Request $request, $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Menu tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
        ]);

        $menu->update($request->only(['name', 'price', 'category', 'stock']));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'edit_menu',
            'description' => 'Mengedit menu: ' . $menu->name,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu berhasil diupdate',
            'data' => $menu
        ]);
    }

    // 4. Hapus menu
    public function deleteMenu(Request $request, $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Menu tidak ditemukan'
            ], 404);
        }

        $menuName = $menu->name;
        $menu->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'hapus_menu',
            'description' => 'Menghapus menu: ' . $menuName,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu berhasil dihapus'
        ]);
    }

    // ============ LAPORAN TRANSAKSI ============

    // 5. Lihat semua transaksi (semua kasir)
    public function allTransactions(Request $request)
    {
        $query = Transaction::with('user', 'items.menu', 'table');

        // Filter by kasir
        if ($request->has('kasir_id')) {
            $query->where('user_id', $request->kasir_id);
        }

        // Filter by tanggal
        if ($request->has('tanggal')) {
            $query->whereDate('payment_time', $request->tanggal);
        }

        // Filter by bulan (YYYY-MM)
        if ($request->has('bulan')) {
            $query->whereYear('payment_time', substr($request->bulan, 0, 4))
                ->whereMonth('payment_time', substr($request->bulan, 5, 2));
        }

        $transactions = $query->orderBy('payment_time', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    // 6. Laporan pendapatan harian
    public function dailyReport(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date'
        ]);

        $total = Transaction::whereDate('payment_time', $request->tanggal)
            ->sum('total_amount');

        $count = Transaction::whereDate('payment_time', $request->tanggal)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tanggal' => $request->tanggal,
                'total_transaksi' => $count,
                'total_pendapatan' => $total,
                'pendapatan_format' => 'Rp ' . number_format($total, 0, ',', '.')
            ]
        ]);
    }

    // 7. Laporan pendapatan bulanan
    public function monthlyReport(Request $request)
    {
        $request->validate([
            'bulan' => 'required|date_format:Y-m'
        ]);

        $total = Transaction::whereYear('payment_time', substr($request->bulan, 0, 4))
            ->whereMonth('payment_time', substr($request->bulan, 5, 2))
            ->sum('total_amount');

        $count = Transaction::whereYear('payment_time', substr($request->bulan, 0, 4))
            ->whereMonth('payment_time', substr($request->bulan, 5, 2))
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'bulan' => $request->bulan,
                'total_transaksi' => $count,
                'total_pendapatan' => $total,
                'pendapatan_format' => 'Rp ' . number_format($total, 0, ',', '.')
            ]
        ]);
    }

    // 8. Daftar semua kasir (untuk filter)
    public function getKasirList()
    {
        $kasirs = User::where('role', 'kasir')->get(['id', 'name', 'email']);
        return response()->json([
            'status' => 'success',
            'data' => $kasirs
        ]);
    }

    // ============ LOG AKTIVITAS ============

    // 9. Lihat semua log aktivitas
    public function activityLogs(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }
}
