<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface StageServiceInterface
{
    public function getAllStages(): Collection;

    public function getStage(int $id): ?object;

    public function getStageTasks(int $stageId): Collection;
}
