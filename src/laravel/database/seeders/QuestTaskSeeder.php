<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestTaskSeeder extends Seeder
{
    public function run(): void
    {
        $stages = DB::table('quest_stages')->pluck('id', 'code')->toArray();
        $now = now();

        $tasks = [
            [
                'quest_stage_id' => $stages['escape_room_lecture'] ?? null,
                'title' => 'Найти скрытый фрагмент спектрограммы',
                'description' => 'Соберите ответ из нескольких фрагментов с учетом ложных следов.',
                'task_type' => 'puzzle',
                'difficulty' => 'hard',
                'order' => 1,
                'max_score' => 20,
                'payload' => json_encode(['steps' => 3, 'hints' => 2]),
                'required' => true,
            ],
            [
                'quest_stage_id' => $stages['inclusive_design'] ?? null,
                'title' => 'Разработать адаптационные решения',
                'description' => 'Определите барьеры и предложите адаптации для студента с нарушением зрения.',
                'task_type' => 'case',
                'difficulty' => 'medium',
                'order' => 1,
                'max_score' => 15,
                'payload' => json_encode(['scenario' => 'vision_impairment']),
                'required' => true,
            ],
            [
                'quest_stage_id' => $stages['effective_communication'] ?? null,
                'title' => 'Провести профилактическую беседу',
                'description' => 'Выберите стратегию общения с демотивированным студентом.',
                'task_type' => 'dialogue',
                'difficulty' => 'medium',
                'order' => 1,
                'max_score' => 15,
                'payload' => json_encode(['student' => 'unmotivated']),
                'required' => true,
            ],
            [
                'quest_stage_id' => $stages['event_organization'] ?? null,
                'title' => 'Собрать план профилактического мероприятия',
                'description' => 'Выберите тему, формат и ресурсы для группы.',
                'task_type' => 'planner',
                'difficulty' => 'medium',
                'order' => 1,
                'max_score' => 20,
                'payload' => json_encode(['themes' => ['stress-management', 'team-building', 'career-guidance']]),
                'required' => true,
            ],
            [
                'quest_stage_id' => $stages['crisis_intervention'] ?? null,
                'title' => 'Оценить сценарий кризиса',
                'description' => 'Опишите последовательность шагов в случае суицидального риска студента.',
                'task_type' => 'scenario',
                'difficulty' => 'hard',
                'order' => 1,
                'max_score' => 20,
                'payload' => json_encode(['scenario' => 'suicide_risk']),
                'required' => true,
            ],
        ];

        foreach ($tasks as $task) {
            if ($task['quest_stage_id'] === null) {
                continue;
            }

            DB::table('quest_tasks')->updateOrInsert(
                [
                    'quest_stage_id' => $task['quest_stage_id'],
                    'title' => $task['title'],
                ],
                [
                    'description' => $task['description'],
                    'task_type' => $task['task_type'],
                    'difficulty' => $task['difficulty'],
                    'order' => $task['order'],
                    'max_score' => $task['max_score'],
                    'payload' => $task['payload'],
                    'required' => $task['required'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
