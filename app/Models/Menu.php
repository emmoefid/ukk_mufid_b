<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'price',
        'category',
        'stock',
    ];

    // Relasi: menu punya banyak transaction items
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
