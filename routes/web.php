<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('hospitais', \App\Http\Controllers\HospitalController::class);
Route::resource('funcionarios', \App\Http\Controllers\FuncionarioController::class);
Route::resource('pacientes', \App\Http\Controllers\PacienteController::class);
Route::resource('atendimentos', \App\Http\Controllers\AtendimentoController::class);
