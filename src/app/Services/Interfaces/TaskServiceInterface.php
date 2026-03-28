<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
    public function getAllTasks(): Collection;

    public function getTask(int $id): ?object;

    public function createTask(array $data): object;

    public function updateTask(int $id, array $data): ?object;

    public function deleteTask(int $id): bool;
}
