<?php

use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/consultas');
Route::resource('consultas', ConsultaController::class);
Route::post('consultas/{consulta}/cancelar', [ConsultaController::class, 'cancelar'])->name('consultas.cancelar');
Route::post('consultas/{consulta}/realizar', [ConsultaController::class, 'realizar'])->name('consultas.realizar');
Route::get('relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
