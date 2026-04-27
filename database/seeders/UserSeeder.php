<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Cafe',
            'email' => 'admin@cafe.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Manajer
        User::create([
            'name' => 'Manajer Cafe',
            'email' => 'manajer@cafe.com',
            'password' => Hash::make('password'),
            'role' => 'manajer',
        ]);

        // Kasir 1
        User::create([
            'name' => 'Kasir Ahmad',
            'email' => 'kasir1@cafe.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // Kasir 2
        User::create([
            'name' => 'Kasir Budi',
            'email' => 'kasir2@cafe.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);
    }
}
