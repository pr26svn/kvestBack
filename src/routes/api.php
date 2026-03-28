<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\TaskController;

Route::get('stages', [StageController::class, 'index']);
Route::get('stages/{stageId}', [StageController::class, 'show']);
Route::get('stages/{stageId}/tasks', [StageController::class, 'tasks']);

Route::get('tasks', [TaskController::class, 'index']);
Route::get('tasks/{taskId}', [TaskController::class, 'show']);

Route::prefix('admin')->group(function () {
    Route::post('stages', [StageController::class, 'store']);
    Route::put('stages/{stageId}', [StageController::class, 'update']);
    Route::delete('stages/{stageId}', [StageController::class, 'destroy']);

    Route::post('tasks', [TaskController::class, 'store']);
    Route::put('tasks/{taskId}', [TaskController::class, 'update']);
    Route::delete('tasks/{taskId}', [TaskController::class, 'destroy']);
});

Route::post('submissions', [SubmissionController::class, 'store']);
Route::get('users/{userId}/progress', [SubmissionController::class, 'userProgress']);

Route::post('submissions/{submissionId}/assessments', [AssessmentController::class, 'store']);
