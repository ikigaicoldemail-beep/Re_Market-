<?php

use App\Http\Controllers\Api\V1\StoreController;
use Illuminate\Support\Facades\Route;

Route::post('/stores', [StoreController::class, 'store']);
Route::put('/stores/{store}', [StoreController::class, 'update']);
