<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisualizadorController;

// Redireciona a raiz para o visualizador
Route::redirect('/', '/visualizar');

// Rota principal com 3 parâmetros opcionais: banco, schema e tabela
Route::get('/visualizar/{banco?}/{schema?}/{tabela?}', [VisualizadorController::class, 'index'])->name('visualizar');

// Rota para deletar registros
Route::delete('/visualizar/{banco?}/{schema?}/{tabela?}', [VisualizadorController::class, 'destroy'])->name('visualizar.destroy');

// Rota para atualizar registros
Route::put('/visualizar/{banco?}/{schema?}/{tabela?}', [VisualizadorController::class, 'update'])->name('visualizar.update');

// Rota para histórico do trabalhador
Route::get('/{banco}/trabalhador/{identificador}/historico', [VisualizadorController::class, 'historicoTrabalhador'])->name('trabalhador.historico');

