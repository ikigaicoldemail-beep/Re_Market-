<?php

use App\Http\Controllers\Api\V1\VisualSearchController;
use Illuminate\Support\Facades\Route;

Route::post('/search/visual', VisualSearchController::class)
    ->middleware('throttle:public-search');

Route::prefix('v1')
    ->middleware('throttle:api')
    ->group(function () {
        require base_path('routes/api/v1.php');
    });
