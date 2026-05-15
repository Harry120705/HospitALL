<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ─── CRUD padrão ──────────────────────────────────────────────────
Route::apiResource('hospitais',    \App\Http\Controllers\HospitalController::class);
Route::apiResource('funcionarios', \App\Http\Controllers\FuncionarioController::class);
Route::apiResource('pacientes',    \App\Http\Controllers\PacienteController::class);
Route::apiResource('atendimentos', \App\Http\Controllers\AtendimentoController::class);

// ─── Operações NoSQL em sub-documentos ────────────────────────────
// Push de setor no array embebido de HOSPITAIS
Route::post('hospitais/{id}/setores', [\App\Http\Controllers\NoSQLController::class, 'adicionarSetor']);

// Push de evolução médica no array de ATENDIMENTOS
Route::post('atendimentos/{id}/evolucoes', [\App\Http\Controllers\NoSQLController::class, 'adicionarEvolucao']);

// Filtro de atendimentos por setor_id (sobrepõe index com query params)
Route::get('atendimentos/por-setor', [\App\Http\Controllers\NoSQLController::class, 'atendimentosPorSetor']);
