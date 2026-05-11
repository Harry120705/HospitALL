<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('hospitais', \App\Http\Controllers\HospitalController::class);
Route::apiResource('funcionarios', \App\Http\Controllers\FuncionarioController::class);
Route::apiResource('pacientes', \App\Http\Controllers\PacienteController::class);
Route::apiResource('atendimentos', \App\Http\Controllers\AtendimentoController::class);
