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
            ['email' => 'admin@test.com'],
            [
                'name' => 'Администратор',
                'display_name' => 'Админ',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
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
                'role' => 'user',
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
                'role' => 'user',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Модератор',
                'display_name' => 'Модератор',
                'email' => 'moderator@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Тестовые участники для демонстрации
        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->updateOrInsert(
                ['email' => "user{$i}@test.com"],
                [
                    'name' => "Пользователь {$i}",
                    'display_name' => "User {$i}",
                    'email' => "user{$i}@test.com",
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
