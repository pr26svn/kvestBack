<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'code' => 'escape_room_lecture',
                'title' => 'Этап 1: Лекционный зал',
                'description' => 'Escape Room, проверка наблюдательности и аналитики.',
                'order' => 1,
                'stage_type' => 'escape_room',
                'deadline_at' => '2025-11-10',
            ],
            [
                'code' => 'inclusive_design',
                'title' => 'Модуль 2: Проектирование инклюзивной среды',
                'description' => 'Практика по адаптивным образовательным траекториям.',
                'order' => 2,
                'stage_type' => 'case_module',
                'deadline_at' => '2025-11-10',
            ],
            [
                'code' => 'effective_communication',
                'title' => 'Модуль 3: Эффективная коммуникация',
                'description' => 'Интерактивные диалоги в коридорах академии.',
                'order' => 3,
                'stage_type' => 'dialogue_module',
                'deadline_at' => '2025-12-31',
            ],
            [
                'code' => 'event_organization',
                'title' => 'Модуль 4: Организация воспитательной работы',
                'description' => 'Конструктор профилактического мероприятия.',
                'order' => 4,
                'stage_type' => 'project_module',
                'deadline_at' => '2026-02-28',
            ],
            [
                'code' => 'crisis_intervention',
                'title' => 'Модуль 5: Кризисное вмешательство',
                'description' => 'Сценарии экстренных ситуаций и корректные действия.',
                'order' => 5,
                'stage_type' => 'crisis_module',
                'deadline_at' => '2026-03-31',
            ],
        ];

        foreach ($stages as $stage) {
            DB::table('quest_stages')->updateOrInsert(
                ['code' => $stage['code']],
                [
                    'title' => $stage['title'],
                    'description' => $stage['description'],
                    'order' => $stage['order'],
                    'stage_type' => $stage['stage_type'],
                    'deadline_at' => $stage['deadline_at'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
