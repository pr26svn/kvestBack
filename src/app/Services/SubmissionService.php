<?php

namespace App\Services;

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

        return TaskSubmission::create($data);
    }

    public function getUserProgress(int $userId): Collection
    {
        return TaskSubmission::with(['task.stage', 'assessments'])
            ->where('user_id', $userId)
            ->orderBy('submitted_at', 'desc')
            ->get();
    }
}
