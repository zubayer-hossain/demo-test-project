<?php

use App\Http\Controllers\DemoTestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('demo/test')->group(function () {
    Route::post('/', [DemoTestController::class, 'store'])->name('demo.test.store');
    Route::post('/activate', [DemoTestController::class, 'activate'])->name('demo.test.activate');
    Route::post('/deactivate', [DemoTestController::class, 'deactivate'])->name('demo.test.deactivate');
});
