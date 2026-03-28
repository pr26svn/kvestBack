<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'administrator', 'label' => 'Администратор', 'description' => 'Управление платформой'],
            ['name' => 'curator', 'label' => 'Куратор', 'description' => 'Участник конкурса'],
            ['name' => 'expert', 'label' => 'Эксперт', 'description' => 'Оценка заданий'],
            ['name' => 'moderator', 'label' => 'Модератор', 'description' => 'Модерация открытых ответов'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'label' => $role['label'],
                    'description' => $role['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $roleMap = DB::table('roles')->pluck('id', 'name')->toArray();
        $users = DB::table('users')->whereIn('email', [
            'admin@example.com',
            'curator@example.com',
            'expert@example.com',
            'moderator@example.com',
        ])->get();

        foreach ($users as $user) {
            $roleName = match ($user->email) {
                'admin@example.com' => 'administrator',
                'curator@example.com' => 'curator',
                'expert@example.com' => 'expert',
                'moderator@example.com' => 'moderator',
                default => null,
            };

            if ($roleName && isset($roleMap[$roleName])) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $roleMap[$roleName]],
                    []
                );
            }
        }
    }
}
