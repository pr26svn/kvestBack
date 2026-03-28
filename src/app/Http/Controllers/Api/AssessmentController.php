<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $assessment = $this->assessmentService->createAssessment($submissionId, $data);

        return response()->json(['data' => $assessment], 201);
    }
}
