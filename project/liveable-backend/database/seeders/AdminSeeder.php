<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@liveable.com'],
            [
                'name' => 'Admin',
                'last_name' => 'Liveable',
                'email' => 'admin@liveable.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'share_socials' => false,
            ]
        );
    }
}
