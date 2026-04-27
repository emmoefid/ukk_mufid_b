<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Table::create([
                'table_number' => 'Meja ' . $i,
                'status' => 'available',
            ]);
        }

        // Tambah meja outdoor
        Table::create([
            'table_number' => 'Outdoor 1',
            'status' => 'available',
        ]);

        Table::create([
            'table_number' => 'Outdoor 2',
            'status' => 'available',
        ]);
    }
}
