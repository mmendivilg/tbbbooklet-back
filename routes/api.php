<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\ArchivoUbicacionController;

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

Route::post('register', [PassportAuthController::class, 'register']);

Route::post('login', [PassportAuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('logout', [PassportAuthController::class, 'logout']);
});


Route::middleware('auth:api')->group(function () {
    Route::prefix('ubicaciones')->group(function () {
        Route::get('/', [UbicacionController::class, 'index']);
        Route::get('/{id}', [UbicacionController::class, 'show']);
        Route::post('/', [UbicacionController::class, 'store']);
        Route::put('/{id}', [UbicacionController::class, 'update']);
        Route::delete('/{id}', [UbicacionController::class, 'destroy']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('archivo/ubicaciones')->group(function () {
        Route::get('/{id}', [ArchivoUbicacionController::class, 'show']);
        Route::post('/', [ArchivoUbicacionController::class, 'store']);
        Route::delete('/{id_ubicacion}/{id_archivo}', [ArchivoUbicacionController::class, 'destroy']);
    });
});
