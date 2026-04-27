<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = [
        'table_number',
        'status',
    ];

    // Relasi: meja punya banyak transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
