<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'display_name' => 'Админ',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'curator@example.com'],
            [
                'name' => 'Куратор',
                'display_name' => 'Куратор',
                'email' => 'curator@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'expert@example.com'],
            [
                'name' => 'Эксперт',
                'display_name' => 'Эксперт',
                'email' => 'expert@example.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
