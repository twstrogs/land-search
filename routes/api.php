<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\PromptController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\PipelineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes v1
|--------------------------------------------------------------------------
|
| Land Search API v1 endpoints
|
*/

Route::prefix('v1')->group(function () {
    // Posts
    Route::get('/posts', [ApiController::class, 'search']);
    Route::get('/posts/{id}', [ApiController::class, 'show'])->where('id', '[0-9]+');

    // Metadata
    Route::get('/metadata', [ApiController::class, 'metadata']);

    // AI Extraction
    Route::post('/extract', [ApiController::class, 'extract']);
});

/*
|--------------------------------------------------------------------------
| AI System API Routes
|--------------------------------------------------------------------------
|
| AI Pipeline, Evaluation, Prompt Management, and Semantic Search endpoints
|
*/

Route::prefix('evaluation')->group(function () {
    Route::post('/benchmark', [EvaluationController::class, 'runBenchmark']);
    Route::post('/run', [EvaluationController::class, 'runEvaluation']);
    Route::get('/history', [EvaluationController::class, 'history']);
    Route::get('/comparison', [EvaluationController::class, 'comparison']);
    Route::get('/datasets', [EvaluationController::class, 'datasets']);
    Route::post('/datasets', [EvaluationController::class, 'createDataset']);
    Route::get('/datasets/{id}/field-metrics', [EvaluationController::class, 'fieldMetrics']);
});

Route::prefix('prompts')->group(function () {
    Route::get('/', [PromptController::class, 'index']);
    Route::post('/', [PromptController::class, 'store']);
    Route::get('/active', [PromptController::class, 'active']);
    Route::get('/task-types', [PromptController::class, 'taskTypes']);
    Route::get('/{id}', [PromptController::class, 'show']);
    Route::put('/{id}', [PromptController::class, 'update']);
    Route::delete('/{id}', [PromptController::class, 'destroy']);
    Route::post('/{id}/activate', [PromptController::class, 'activate']);
    Route::post('/{id}/duplicate', [PromptController::class, 'duplicate']);
    Route::post('/{id}/examples', [PromptController::class, 'updateExamples']);
    Route::get('/examples', [PromptController::class, 'examples']);
});

Route::prefix('search')->group(function () {
    Route::get('/semantic', [SearchController::class, 'semantic']);
    Route::post('/index', [SearchController::class, 'indexPost']);
    Route::post('/index-batch', [SearchController::class, 'indexBatch']);
    Route::get('/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/embedding-info', [SearchController::class, 'embeddingInfo']);
    Route::get('/stats', [SearchController::class, 'indexStats']);
});

Route::prefix('pipeline')->group(function () {
    Route::post('/extract', [PipelineController::class, 'extract']);
    Route::post('/extract-batch', [PipelineController::class, 'extractBatch']);
    Route::get('/status', [PipelineController::class, 'status']);
    Route::get('/stats', [PipelineController::class, 'stats']);
    Route::get('/logs', [PipelineController::class, 'logs']);
});

