<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\SubmissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function __construct(protected SubmissionServiceInterface $submissionService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'quest_task_id' => 'required|integer|exists:quest_tasks,id',
            'user_id' => 'required|integer|exists:users,id',
            'answer_text' => 'nullable|string',
            'answer_data' => 'nullable|array',
            'status' => 'nullable|string',
            'score' => 'nullable|numeric',
            'feedback' => 'nullable|string',
        ]);

        $submission = $this->submissionService->createSubmission($data);

        return response()->json(['data' => $submission], 201);
    }

    public function userProgress(int $userId): JsonResponse
    {
        return response()->json(['data' => $this->submissionService->getUserProgress($userId)]);
    }
}
