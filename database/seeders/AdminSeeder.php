<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@payground.com'],
            [
                'name' => 'Root Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '1234567890',
            ]
        );
    }
}
