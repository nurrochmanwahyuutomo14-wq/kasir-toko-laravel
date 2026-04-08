<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat akun admin secara manual tanpa fitur Faker
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Login pakai email ini
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'), // Ini passwordnya: 12345678 (Bisa Mas Nur ganti)
            ]
        );
    }
}
