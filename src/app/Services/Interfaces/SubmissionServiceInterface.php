<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface SubmissionServiceInterface
{
    public function createSubmission(array $data): object;

    public function getUserProgress(int $userId): Collection;
}
