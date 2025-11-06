<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@infomodel.com',
            'password' => bcrypt('admin123'), // Password: admin123
            'is_admin' => true,
        ]);
    }
}
