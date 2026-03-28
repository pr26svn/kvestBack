<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface StageServiceInterface
{
    public function getAllStages(): Collection;

    public function getStage(int $id): ?object;

    public function getStageTasks(int $stageId): Collection;

    public function getStageTasksForUser(int $stageId, int $userId): array;

    public function createStage(array $data): object;

    public function updateStage(int $id, array $data): ?object;

    public function deleteStage(int $id): bool;
}
