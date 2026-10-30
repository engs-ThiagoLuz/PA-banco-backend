<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsultaController;

Route::redirect('/', '/consultas');
Route::resource('consultas', ConsultaController::class);
