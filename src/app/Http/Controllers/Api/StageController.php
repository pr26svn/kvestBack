<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\StageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(protected StageServiceInterface $stageService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->stageService->getAllStages()]);
    }

    public function show(int $stageId): JsonResponse
    {
        $stage = $this->stageService->getStage($stageId);

        if ($stage === null) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        return response()->json(['data' => $stage]);
    }

    public function tasks(int $stageId): JsonResponse
    {
        $tasks = $this->stageService->getStageTasks($stageId);

        return response()->json(['data' => $tasks]);
    }
}
