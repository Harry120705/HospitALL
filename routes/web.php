<?php

use Illuminate\Support\Facades\Route;

// SPA entry point — Vue Router handles all frontend routes
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api|storage|_debugbar).*$');

Route::resource('hospitais', \App\Http\Controllers\HospitalController::class);
Route::resource('funcionarios', \App\Http\Controllers\FuncionarioController::class);
Route::resource('pacientes', \App\Http\Controllers\PacienteController::class);
Route::resource('atendimentos', \App\Http\Controllers\AtendimentoController::class);
