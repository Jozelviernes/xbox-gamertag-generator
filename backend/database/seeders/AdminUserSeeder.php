<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'xboxgamegenerator@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('xboxgenerator.admin'),
                'email_verified_at' => now(),
            ]
        );
    }
}