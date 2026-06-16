<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;

/*
|--------------------------------------------------------------------------
| API Routes — gabylis/api-foundation demo
|--------------------------------------------------------------------------
*/

Route::apiResource('categories', CategoryApiController::class);
Route::apiResource('products', ProductApiController::class);

// Products nested under category
Route::get('categories/{id}/products', [ProductApiController::class, 'index'])
    ->name('categories.products');
