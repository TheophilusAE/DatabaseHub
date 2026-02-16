<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataRecordController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UserController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root to login
Route::get('/', function () {
    if (session()->has('authenticated')) {
        $user = session('user');
        return redirect()->route($user['role'] === 'admin' ? 'admin.dashboard' : 'user.dashboard');
    }
    return redirect()->route('login');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management - Full CRUD (Admin Only)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::get('/{id}/permissions', [UserController::class, 'permissions'])->name('permissions');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk-delete');
    });
    
    // Data Records - Full CRUD
    Route::prefix('data-records')->name('data-records.')->group(function () {
        Route::get('/', [DataRecordController::class, 'index'])->name('index');
        Route::get('/create', [DataRecordController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [DataRecordController::class, 'edit'])->name('edit');
    });

    // Documents - Full CRUD
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

    // Multi-Table Features
    Route::prefix('multi-table')->name('multi-table.')->group(function () {
        Route::get('/hub', [\App\Http\Controllers\MultiTableController::class, 'hub'])->name('hub');
        Route::get('/databases', [\App\Http\Controllers\MultiTableController::class, 'databases'])->name('databases');
        Route::get('/tables', [\App\Http\Controllers\MultiTableController::class, 'tables'])->name('tables');
        Route::get('/joins', [\App\Http\Controllers\MultiTableController::class, 'joins'])->name('joins');
        Route::get('/import-mappings', [\App\Http\Controllers\MultiTableController::class, 'importMappings'])->name('import-mappings');
        Route::get('/export-configs', [\App\Http\Controllers\MultiTableController::class, 'exportConfigs'])->name('export-configs');
        Route::get('/import', [\App\Http\Controllers\MultiTableController::class, 'import'])->name('import');
        Route::get('/export', [\App\Http\Controllers\MultiTableController::class, 'export'])->name('export');
    });

    // Simple Multi-Table Operations
    Route::prefix('simple-multi')->name('simple-multi.')->group(function () {
        Route::get('/view-tables', [\App\Http\Controllers\SimpleMultiTableController::class, 'viewTables'])->name('view-tables');
        Route::get('/multi-upload', [\App\Http\Controllers\SimpleMultiTableController::class, 'multiUpload'])->name('multi-upload');
        Route::get('/selective-export', [\App\Http\Controllers\SimpleMultiTableController::class, 'selectiveExport'])->name('selective-export');
    });
});

// User Routes
Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Data Records - Full CRUD for users
    Route::prefix('data-records')->name('data-records.')->group(function () {
        Route::get('/', [DataRecordController::class, 'index'])->name('index');
        Route::get('/create', [DataRecordController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [DataRecordController::class, 'edit'])->name('edit');
    });

    // Documents - Users can upload
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
    });

    // Import - Users can import data
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::get('/history', [ImportController::class, 'history'])->name('history');
    });

    // Export - Users can export data
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');

    // Multi-Table Features
    Route::prefix('multi-table')->name('multi-table.')->group(function () {
        Route::get('/hub', [\App\Http\Controllers\MultiTableController::class, 'hub'])->name('hub');
        Route::get('/databases', [\App\Http\Controllers\MultiTableController::class, 'databases'])->name('databases');
        Route::get('/tables', [\App\Http\Controllers\MultiTableController::class, 'tables'])->name('tables');
        Route::get('/joins', [\App\Http\Controllers\MultiTableController::class, 'joins'])->name('joins');
        Route::get('/import-mappings', [\App\Http\Controllers\MultiTableController::class, 'importMappings'])->name('import-mappings');
        Route::get('/export-configs', [\App\Http\Controllers\MultiTableController::class, 'exportConfigs'])->name('export-configs');
        Route::get('/import', [\App\Http\Controllers\MultiTableController::class, 'import'])->name('import');
        Route::get('/export', [\App\Http\Controllers\MultiTableController::class, 'export'])->name('export');
    });

    // Simple Multi-Table Operations
    Route::prefix('simple-multi')->name('simple-multi.')->group(function () {
        Route::get('/view-tables', [\App\Http\Controllers\SimpleMultiTableController::class, 'viewTables'])->name('view-tables');
        Route::get('/multi-upload', [\App\Http\Controllers\SimpleMultiTableController::class, 'multiUpload'])->name('multi-upload');
        Route::get('/selective-export', [\App\Http\Controllers\SimpleMultiTableController::class, 'selectiveExport'])->name('selective-export');
    });
});

// Shared routes for backwards compatibility (will redirect based on role)
Route::middleware(['auth'])->group(function () {
    Route::get('/data-records', function () {
        $user = session('user');
        return redirect()->route($user['role'] === 'admin' ? 'admin.data-records.index' : 'user.data-records.index');
    })->name('data-records.index');
    
    Route::get('/documents', function () {
        $user = session('user');
        return redirect()->route($user['role'] === 'admin' ? 'admin.documents.index' : 'user.documents.index');
    })->name('documents.index');
});
