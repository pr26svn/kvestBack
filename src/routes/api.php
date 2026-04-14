<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\StageContentController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;

// Публичные маршруты авторизации
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::get('stages', [StageController::class, 'index']);
Route::get('stages/{stageId}', [StageController::class, 'show']);
Route::get('stages/{stageId}/tasks', [StageController::class, 'tasks']);
Route::get('stages/{stageId}/content', [StageContentController::class, 'show']);

Route::get('tasks', [TaskController::class, 'index']);
Route::get('tasks/{taskId}', [TaskController::class, 'show']);

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Авторизация
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Команды
    Route::get('teams/rankings', [TeamController::class, 'rankings']);
    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::get('teams/{team}', [TeamController::class, 'show']);
    Route::put('teams/{team}', [TeamController::class, 'update']);
    Route::delete('teams/{team}', [TeamController::class, 'destroy']);
    Route::post('teams/{team}/join', [TeamController::class, 'join']);
    Route::get('teams/{team}/members', [TeamController::class, 'members']);
    Route::get('teams/{team}/progress', [TeamController::class, 'progress']);
    Route::get('users/teams', [TeamController::class, 'userTeams']);

    // Админ
    Route::prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);

        Route::post('teams/{team}/add-user', [TeamController::class, 'addUser']);
        Route::delete('teams/{team}/remove-user/{user}', [TeamController::class, 'removeUser']);

        Route::post('stages', [StageController::class, 'store']);
        Route::put('stages/{stageId}', [StageController::class, 'update']);
        Route::delete('stages/{stageId}', [StageController::class, 'destroy']);

        Route::post('tasks', [TaskController::class, 'store']);
        Route::put('tasks/{taskId}', [TaskController::class, 'update']);
        Route::delete('tasks/{taskId}', [TaskController::class, 'destroy']);

        // Content Management for Stages
        Route::get('stages/{stageId}/content', [StageContentController::class, 'show']);
        Route::post('stages/{stageId}/content', [StageContentController::class, 'store']);
        Route::put('stages/{stageId}/content', [StageContentController::class, 'update']);
        Route::delete('stages/{stageId}/content', [StageContentController::class, 'destroy']);
    });

    Route::post('submissions', [SubmissionController::class, 'store']);
    Route::get('users/{userId}/progress', [SubmissionController::class, 'userProgress']);

    Route::post('submissions/{submissionId}/assessments', [AssessmentController::class, 'store']);
});
