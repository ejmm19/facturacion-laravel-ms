<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClienteController;
use App\Http\Controllers\API\ProductoController;
use App\Http\Controllers\API\FacturaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('productos', ProductoController::class);

    Route::get('/facturas/estadisticas', [FacturaController::class, 'estadisticas']);
    Route::get('/facturas/numero/{numero}', [FacturaController::class, 'buscarPorNumero']);
    Route::apiResource('facturas', FacturaController::class);
});

