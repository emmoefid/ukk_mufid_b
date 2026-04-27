<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    // 1. Lihat daftar menu
    public function getMenu()
    {
        $menus = Menu::all();
        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    // 2. Lihat daftar meja yang tersedia
    public function getAvailableTables()
    {
        $tables = Table::where('status', 'available')->get();
        return response()->json([
            'status' => 'success',
            'data' => $tables
        ]);
    }

    // 3. Buat transaksi baru (simpan sementara sebelum bayar)
    public function createTransaction(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'cash_amount' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        $cashAmount = $request->cash_amount;
        $items = $request->items;
        $totalAmount = 0;
        $transactionItems = [];

        // Hitung total dan siapkan data item
        foreach ($items as $item) {
            $menu = Menu::find($item['menu_id']);
            $subtotal = $menu->price * $item['quantity'];
            $totalAmount += $subtotal;

            $transactionItems[] = [
                'menu_id' => $menu->id,
                'quantity' => $item['quantity'],
                'price_at_time' => $menu->price,
                'subtotal' => $subtotal,
            ];
        }

        // Cek apakah uang cukup
        if ($cashAmount < $totalAmount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Uang tidak cukup. Total: Rp ' . number_format($totalAmount, 0, ',', '.')
            ], 400);
        }

        $changeAmount = $cashAmount - $totalAmount;
        $invoiceNumber = 'INV-' . date('YmdHis') . '-' . rand(100, 999);

        DB::beginTransaction();
        try {
            // Buat transaksi
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'table_id' => $request->table_id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'cash_amount' => $cashAmount,
                'change_amount' => $changeAmount,
                'payment_time' => now(),
            ]);

            // Buat item transaksi
            foreach ($transactionItems as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionItem::create($item);
            }

            // Update status meja menjadi occupied
            Table::where('id', $request->table_id)->update(['status' => 'occupied']);

            // Kurangi stok menu
            foreach ($items as $item) {
                Menu::where('id', $item['menu_id'])->decrement('stock', $item['quantity']);
            }

            // Catat log aktivitas
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'transaksi',
                'description' => 'Membuat transaksi ' . $invoiceNumber . ' dengan total Rp ' . number_format($totalAmount, 0, ',', '.'),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil',
                'data' => [
                    'transaction' => $transaction->load('items.menu'),
                    'change_amount' => $changeAmount,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. Lihat riwayat transaksi kasir yang login
    public function myTransactions(Request $request)
    {
        $user = $request->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->with('items.menu', 'table')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    // 5. Lihat detail satu transaksi
    public function transactionDetail($id, Request $request)
    {
        $user = $request->user();
        $transaction = Transaction::where('user_id', $user->id)
            ->with('items.menu', 'table')
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }
}
