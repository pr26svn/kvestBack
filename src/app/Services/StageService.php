<?php

namespace App\Services;

use App\Models\QuestStage;
use App\Models\QuestTask;
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
}
