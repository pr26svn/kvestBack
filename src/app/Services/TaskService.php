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
}
