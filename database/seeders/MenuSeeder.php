<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // Makanan
            ['name' => 'Nasi Goreng', 'price' => 25000, 'category' => 'Makanan', 'stock' => 50],
            ['name' => 'Mie Goreng', 'price' => 22000, 'category' => 'Makanan', 'stock' => 50],
            ['name' => 'Ayam Bakar', 'price' => 35000, 'category' => 'Makanan', 'stock' => 40],
            ['name' => 'Ayam Geprek', 'price' => 28000, 'category' => 'Makanan', 'stock' => 45],
            ['name' => 'Sandwich', 'price' => 18000, 'category' => 'Makanan', 'stock' => 30],
            
            // Minuman
            ['name' => 'Kopi Hitam', 'price' => 15000, 'category' => 'Minuman', 'stock' => 100],
            ['name' => 'Kopi Susu', 'price' => 18000, 'category' => 'Minuman', 'stock' => 100],
            ['name' => 'Teh Manis', 'price' => 10000, 'category' => 'Minuman', 'stock' => 100],
            ['name' => 'Jus Jeruk', 'price' => 15000, 'category' => 'Minuman', 'stock' => 60],
            ['name' => 'Matcha Latte', 'price' => 22000, 'category' => 'Minuman', 'stock' => 50],
            
            // Camilan
            ['name' => 'Kentang Goreng', 'price' => 15000, 'category' => 'Camilan', 'stock' => 80],
            ['name' => 'Pisang Goreng', 'price' => 12000, 'category' => 'Camilan', 'stock' => 70],
            ['name' => 'Cireng', 'price' => 10000, 'category' => 'Camilan', 'stock' => 90],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
