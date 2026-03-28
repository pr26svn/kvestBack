<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskSubmission;
use App\Models\User;
use App\Services\Interfaces\AssessmentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(protected AssessmentServiceInterface $assessmentService)
    {
    }

    public function store(int $submissionId, Request $request): JsonResponse
    {
        $data = $request->validate([
            'evaluator_id' => 'required|integer|exists:users,id',
            'score' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'rubric' => 'nullable|array',
        ]);

        $evaluator = User::find($data['evaluator_id']);

        if ($evaluator === null || ! ($evaluator->hasRole('moderator') || $evaluator->hasRole('expert'))) {
            return response()->json(['message' => 'Only moderators or experts may evaluate answers'], 403);
        }

        $submission = TaskSubmission::with('task')->find($submissionId);

        if ($submission === null) {
            return response()->json(['message' => 'Submission not found'], 404);
        }

        if ($submission->task->task_type !== 'essay') {
            return response()->json(['message' => 'Only open answer submissions can be assessed by moderators'], 403);
        }

        $assessment = $this->assessmentService->createAssessment($submissionId, $data);

        return response()->json(['data' => $assessment], 201);
    }
}
