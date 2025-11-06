<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExcelController;

// Public routes - Trang quét barcode (không cần login)
Route::get('/', [ScannerController::class, 'index'])->name('scanner.index');
Route::post('/api/lookup', [ScannerController::class, 'lookup'])->name('api.lookup');
Route::get('/history', [ScannerController::class, 'history'])->name('history.index');

// Admin authentication routes
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Admin routes - Chỉ admin mới vào được
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/upload', [ExcelController::class, 'showUploadForm'])->name('admin.upload');
    Route::post('/upload', [ExcelController::class, 'upload'])->name('admin.upload.post');
});
