<?php

use App\Http\Controllers\Api\V1\AddressController;
use Illuminate\Support\Facades\Route;

Route::get('/addresses', [AddressController::class, 'index']);
Route::post('/addresses', [AddressController::class, 'store']);
Route::put('/addresses/{address}', [AddressController::class, 'update']);
Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
