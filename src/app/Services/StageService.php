<?php

namespace App\Services;

use App\Models\QuestStage;
use App\Models\QuestTask;
use App\Models\TaskSubmission;
use App\Services\Interfaces\StageServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class StageService implements StageServiceInterface
{
    public function getAllStages(): Collection
    {
        return QuestStage::with('tasks')->orderBy('order')->get();
    }

    public function getStage(int $id): ?object
    {
        return QuestStage::with('tasks')->find($id);
    }

    public function getStageTasks(int $stageId): Collection
    {
        return QuestTask::where('quest_stage_id', $stageId)
            ->orderBy('order')
            ->get();
    }

    public function getStageTasksForUser(int $stageId, int $userId): array
    {
        $tasks = QuestTask::where('quest_stage_id', $stageId)
            ->orderBy('order')
            ->get();

        $submissions = TaskSubmission::where('user_id', $userId)
            ->whereIn('quest_task_id', $tasks->pluck('id'))
            ->get()
            ->keyBy('quest_task_id');

        $firstIncomplete = $tasks->search(function ($task) use ($submissions) {
            return !isset($submissions[$task->id]);
        });

        if ($firstIncomplete === false) {
            $firstIncomplete = null;
        }

        return $tasks->map(function ($task, $index) use ($submissions, $firstIncomplete) {
            $submission = $submissions[$task->id] ?? null;

            return [
                'id' => $task->id,
                'quest_stage_id' => $task->quest_stage_id,
                'title' => $task->title,
                'description' => $task->description,
                'task_type' => $task->task_type,
                'difficulty' => $task->difficulty,
                'order' => $task->order,
                'max_score' => $task->max_score,
                'payload' => $task->payload,
                'required' => $task->required,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'completed' => $submission !== null,
                'submission' => $submission ? [
                    'id' => $submission->id,
                    'status' => $submission->status,
                    'score' => $submission->score,
                    'submitted_at' => $submission->submitted_at,
                ] : null,
                'active' => $firstIncomplete !== null && $index === $firstIncomplete,
                'locked' => $firstIncomplete !== null && $index > $firstIncomplete,
            ];
        })->toArray();
    }

    public function createStage(array $data): object
    {
        return QuestStage::create($data);
    }

    public function updateStage(int $id, array $data): ?object
    {
        $stage = QuestStage::find($id);

        if ($stage === null) {
            return null;
        }

        $stage->fill($data);
        $stage->save();

        return $stage;
    }

    public function deleteStage(int $id): bool
    {
        $stage = QuestStage::find($id);

        if ($stage === null) {
            return false;
        }

        QuestTask::where('quest_stage_id', $id)->delete();

        return $stage->delete();
    }
}
