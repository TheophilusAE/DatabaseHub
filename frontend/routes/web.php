<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataRecordController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExportController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Data Records
Route::prefix('data-records')->name('data-records.')->group(function () {
    Route::get('/', [DataRecordController::class, 'index'])->name('index');
    Route::get('/create', [DataRecordController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [DataRecordController::class, 'edit'])->name('edit');
});

// Documents
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/create', [DocumentController::class, 'create'])->name('create');
});

// Import
Route::prefix('import')->name('import.')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::get('/history', [ImportController::class, 'history'])->name('history');
});

// Export
Route::get('/export', [ExportController::class, 'index'])->name('export.index');
