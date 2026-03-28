<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['code' => 'diagnost', 'title' => 'Диагност', 'description' => 'За успешное проявление наблюдательности'],
            ['code' => 'inclusive_pedagogue', 'title' => 'Инклюзивный педагог', 'description' => 'За эффективную работу с ОВЗ'],
            ['code' => 'communicator', 'title' => 'Коммуникатор', 'description' => 'За мастерство в общении'],
            ['code' => 'crisis_manager', 'title' => 'Кризис-менеджер', 'description' => 'За грамотные действия в сложных ситуациях'],
            ['code' => 'mentor_master', 'title' => 'Мастер наставничества', 'description' => 'За полное прохождение квеста'],
        ];

        foreach ($badges as $badge) {
            DB::table('badges')->updateOrInsert(
                ['code' => $badge['code']],
                [
                    'title' => $badge['title'],
                    'description' => $badge['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
