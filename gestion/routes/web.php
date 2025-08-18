<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InquilinoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas para actas
    Route::get('/actas/crear', [ActaController::class, 'create'])->name('actas.create');
    Route::post('/actas', [ActaController::class, 'store'])->name('actas.store');
    Route::get('/actas', [ActaController::class, 'index'])->name('actas.index');
    Route::get('/actas/{acta}/download', [ActaController::class, 'download'])->name('actas.download');
    
    // Rutas para importación de actas
    Route::get('/actas/import', [ActaController::class, 'showImport'])->name('actas.import');
    Route::post('/actas/import', [ActaController::class, 'import'])->name('actas.import.process');
    Route::get('/actas/template', [ActaController::class, 'downloadTemplate'])->name('actas.template');
    
    // Rutas para inquilinos
    Route::resource('inquilinos', InquilinoController::class);
    Route::post('/inquilinos/{inquilino}/pago', [InquilinoController::class, 'procesarPago'])->name('inquilinos.pago');
    
    // Rutas para importación de inquilinos
    Route::get('/inquilinos/import/form', [InquilinoController::class, 'showImport'])->name('inquilinos.import');
    Route::post('/inquilinos/import', [InquilinoController::class, 'import'])->name('inquilinos.import.process');
    Route::get('/inquilinos/template', [InquilinoController::class, 'downloadTemplate'])->name('inquilinos.template');
});

require __DIR__.'/auth.php';