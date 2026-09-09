<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisualizadorController;
use App\Http\Controllers\CertificadoController;

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

// Rotas para certificados
Route::get('/{banco}/certificados', [CertificadoController::class, 'index'])->name('certificados.index');

Route::post('/{banco}/certificados/upload', [CertificadoController::class, 'upload'])->name('certificados.upload');

Route::get('/{banco}/certificados/download', [CertificadoController::class, 'download'])->name('certificados.download');

Route::delete('/{banco}/certificados/destroy', [CertificadoController::class, 'destroy'])->name('certificados.destroy');

