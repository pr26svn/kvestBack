<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
    public function getAllTasks(): Collection;

    public function getTask(int $id): ?object;
}
