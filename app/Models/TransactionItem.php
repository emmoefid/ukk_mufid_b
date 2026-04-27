<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'menu_id',
        'quantity',
        'price_at_time',
        'subtotal',
    ];

    // Relasi: transaction item milik satu transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi: transaction item milik satu menu
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
