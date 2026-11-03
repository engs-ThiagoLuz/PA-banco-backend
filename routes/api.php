<?php

use App\Http\Controllers\Api\ConsultaApiController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('consultas', ConsultaApiController::class)
    ->names('api.consultas');
Route::post('consultas/{consulta}/cancelar', [ConsultaApiController::class, 'cancelar'])->name('api.consultas.cancelar');
Route::post('consultas/{consulta}/realizar', [ConsultaApiController::class, 'realizar'])->name('api.consultas.realizar');
Route::get('relatorios/faturamento', [RelatorioController::class, 'faturamento'])->name('api.relatorios.faturamento');
Route::get('relatorios/ocupacao', [RelatorioController::class, 'ocupacao'])->name('api.relatorios.ocupacao');
