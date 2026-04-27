<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'table_id',
        'invoice_number',
        'total_amount',
        'cash_amount',
        'change_amount',
        'payment_time',
    ];

    protected $casts = [
        'payment_time' => 'datetime',
    ];

    // Relasi: transaksi milik satu user (kasir)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: transaksi milik satu meja
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    // Relasi: transaksi punya banyak item
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
