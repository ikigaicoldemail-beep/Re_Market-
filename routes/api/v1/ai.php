<?php

use App\Http\Controllers\Api\V1\AiSimilaritySearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:ai')->group(function () {
    Route::post('/ai/similarity-search', [AiSimilaritySearchController::class, 'store']);
});
