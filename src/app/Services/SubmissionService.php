<?php

namespace App\Services;

use App\Models\QuestTask;
use App\Models\StageProgress;
use App\Models\TaskSubmission;
use App\Services\Interfaces\SubmissionServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SubmissionService implements SubmissionServiceInterface
{
    public function createSubmission(array $data): object
    {
        $data['submitted_at'] = $data['submitted_at'] ?? Carbon::now();
        $data['status'] = $data['status'] ?? 'submitted';

        $submission = TaskSubmission::create($data);

        $task = QuestTask::find($data['quest_task_id']);

        if ($task !== null) {
            $stageId = $task->quest_stage_id;
            $userId = $data['user_id'];

            $progress = StageProgress::updateOrCreate(
                ['user_id' => $userId, 'quest_stage_id' => $stageId],
                [
                    'started_at' => now(),
                    'status' => 'in_progress',
                    'meta' => ['started_at' => now()->toDateTimeString()],
                ]
            );

            $taskIds = QuestTask::where('quest_stage_id', $stageId)->pluck('id');
            $completedCount = TaskSubmission::where('user_id', $userId)
                ->whereIn('quest_task_id', $taskIds)
                ->distinct('quest_task_id')
                ->count('quest_task_id');
            $totalCount = $taskIds->count();

            $progress->meta = [
                'started_at' => $progress->started_at?->toDateTimeString(),
                'completed_tasks' => $completedCount,
                'total_tasks' => $totalCount,
            ];

            if ($completedCount >= $totalCount && $totalCount > 0) {
                $progress->completed_at = now();
                $progress->status = 'completed';
                $progress->score = TaskSubmission::where('user_id', $userId)
                    ->whereIn('quest_task_id', $taskIds)
                    ->sum('score');
            }

            $progress->save();
        }

        return $submission;
    }

    public function getUserProgress(int $userId): Collection
    {
        return TaskSubmission::with(['task.stage', 'assessments'])
            ->where('user_id', $userId)
            ->orderBy('submitted_at', 'desc')
            ->get();
    }
}
