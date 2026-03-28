<?php

namespace App\Services;

use App\Models\QuestTask;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskService implements TaskServiceInterface
{
    public function getAllTasks(): Collection
    {
        return QuestTask::with('stage')->orderBy('order')->get();
    }

    public function getTask(int $id): ?object
    {
        return QuestTask::with('stage')->find($id);
    }

    public function createTask(array $data): object
    {
        return QuestTask::create($data);
    }

    public function updateTask(int $id, array $data): ?object
    {
        $task = QuestTask::find($id);

        if ($task === null) {
            return null;
        }

        $task->fill($data);
        $task->save();

        return $task;
    }

    public function deleteTask(int $id): bool
    {
        $task = QuestTask::find($id);

        if ($task === null) {
            return false;
        }

        return $task->delete();
    }
}
