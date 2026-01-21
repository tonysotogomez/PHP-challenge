<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para reportes crediticios
Route::prefix('reports')->name('report.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::post('/export', [ReportController::class, 'export'])->name('export');
    Route::get('/status/{jobId}', [ReportController::class, 'checkStatus'])->name('status');
    Route::get('/download/{jobId}', [ReportController::class, 'download'])->name('download');
});