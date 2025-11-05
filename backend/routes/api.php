<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\JobController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {

    // Material Management
    Route::prefix('materials')->group(function () {
        Route::get('/', [MaterialController::class, 'index']);
        Route::post('/', [MaterialController::class, 'store']);
        Route::get('/{materialId}', [MaterialController::class, 'show']);
        Route::post('/{materialId}/generate-digital', [MaterialController::class, 'generateDigital']);
    });

    // Job Management
    Route::prefix('jobs')->group(function () {
        Route::get('/', [JobController::class, 'index']);
        Route::get('/{jobId}', [JobController::class, 'show']);
        Route::post('/{jobId}/retry', [JobController::class, 'retry']);
    });

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/materials/{materialId}/quiz-analysis', [AnalyticsController::class, 'quizAnalysis']);
        Route::get('/materials/{materialId}/performance', [AnalyticsController::class, 'materialPerformance']);
    });

    // Admin-only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User management
        Route::apiResource('users', \App\Http\Controllers\API\Admin\UserController::class);

        // Templates
        Route::apiResource('email-templates', \App\Http\Controllers\API\Admin\EmailTemplateController::class);
        Route::apiResource('assignment-templates', \App\Http\Controllers\API\Admin\AssignmentTemplateController::class);

        // System configuration
        Route::get('system/status', \App\Http\Controllers\API\Admin\SystemController::class, 'status');
        Route::post('system/trigger-sync', \App\Http\Controllers\API\Admin\SystemController::class, 'triggerSync');

        // Audit logs
        Route::get('audit-logs', \App\Http\Controllers\API\Admin\AuditLogController::class, 'index');
    });
});

// Webhook routes for n8n
Route::prefix('webhooks')->group(function () {
    Route::post('/n8n/job-status', [\App\Http\Controllers\WebhookController::class, 'jobStatus']);
    Route::post('/n8n/material-complete', [\App\Http\Controllers\WebhookController::class, 'materialComplete']);
    Route::post('/n8n/schedule-processed', [\App\Http\Controllers\WebhookController::class, 'scheduleProcessed']);
});