<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActaController;
use App\Http\Controllers\ApartamentoController;
use App\Http\Controllers\ReciboGastoComunController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\DeudaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ConciliacionController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\FondoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InquilinoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/dashboard/pdf', [DashboardController::class, 'generatePdf'])->middleware(['auth'])->name('dashboard.pdf');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas para documentos
    Route::get('/actas/crear', [ActaController::class, 'create'])->name('actas.create');
    Route::post('/actas', [ActaController::class, 'store'])->name('actas.store');
    Route::get('/actas', [ActaController::class, 'index'])->name('actas.index');
    Route::get('/actas/{acta}/download', [ActaController::class, 'download'])->name('actas.download');
    Route::delete('/actas/{acta}', [ActaController::class, 'destroy'])->name('actas.destroy');
    
    // Rutas para importación de documentos
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
    
    // Rutas para apartamentos
    // Route::get('/apartamentos/import/form', [ApartamentoController::class, 'showImport'])->name('apartamentos.import');
    // Route::post('/apartamentos/import', [ApartamentoController::class, 'import'])->name('apartamentos.import.process');
    // Route::get('/apartamentos/template', [ApartamentoController::class, 'downloadTemplate'])->name('apartamentos.template');

    Route::resource('apartamentos', ApartamentoController::class);
    
    // Rutas para recibos de gasto común
    Route::get('/recibos/import/form', [ReciboGastoComunController::class, 'import'])->name('recibos.import');
    Route::post('/recibos/import', [ReciboGastoComunController::class, 'importProcess'])->name('recibos.import.process');
    Route::get('/recibos/template', [ReciboGastoComunController::class, 'template'])->name('recibos.template');
    
    // Asignación manual de recibos (DEBE IR ANTES del resource)
    Route::get('/recibos/asignar-manual', [ReciboGastoComunController::class, 'showAsignarRecibosManual'])->name('recibos.asignar-manual');
    Route::post('/recibos/asignar-manual', [ReciboGastoComunController::class, 'asignarRecibosManual'])->name('recibos.asignar-manual.process');
    Route::delete('/recibos/eliminar-asignacion/{recibo}/{apartamento}', [ReciboGastoComunController::class, 'eliminarAsignacion'])->name('recibos.eliminar-asignacion');
    
    Route::get('/recibos/{recibo}/print', [ReciboGastoComunController::class, 'print'])->name('recibos.print');
Route::get('/recibos/{recibo}/descargar-archivo', [ReciboGastoComunController::class, 'descargarArchivo'])->name('recibos.descargar-archivo');
Route::delete('/recibos/destroy-multiple', [ReciboGastoComunController::class, 'destroyMultiple'])->name('recibos.destroy-multiple');
Route::delete('/recibos/destroy-all', [ReciboGastoComunController::class, 'destroyAll'])->name('recibos.destroy-all');
Route::resource('recibos', ReciboGastoComunController::class);
    
    // Rutas para pagos
    Route::resource('pagos', PagoController::class);
    Route::get('/pagos/apartamento/{apartamento}/estado-cuenta', [PagoController::class, 'estadoCuenta'])->name('pagos.estado-cuenta');
    Route::patch('/pagos/{pago}/confirmar', [PagoController::class, 'confirmar'])->name('pagos.confirmar');
    Route::patch('/pagos/{pago}/rechazar', [PagoController::class, 'rechazar'])->name('pagos.rechazar');
    // Rutas para pago global
    Route::get('/pagos/global/create/{apartamento}', [PagoController::class, 'createGlobal'])->name('pagos.create-global');
    Route::post('/pagos/global/store', [PagoController::class, 'storeGlobal'])->name('pagos.store-global');
    
    // Rutas para consulta de deudas
    Route::get('/deudas', [DeudaController::class, 'index'])->name('deudas.index');
    Route::post('/deudas/enviar-correo', [DeudaController::class, 'enviarReportePorCorreo'])->name('deudas.enviar-correo');
    Route::get('/deudas/export/excel', [DeudaController::class, 'exportExcel'])->name('deudas.export.excel');
    Route::get('/api/deudas/estadisticas', [DeudaController::class, 'estadisticas'])->name('api.deudas.estadisticas');
    
    // Rutas para importación CSV de apartamentos
    Route::get('/deudas/import', [DeudaController::class, 'showImport'])->name('deudas.import');
    Route::post('/deudas/import', [DeudaController::class, 'import'])->name('deudas.import.process');
    Route::get('/deudas/template', [DeudaController::class, 'downloadTemplate'])->name('deudas.template');
    
    // Rutas para importación completa de Excel
    Route::get('/deudas/import/completo', [DeudaController::class, 'showImportCompleto'])->name('deudas.import.completo');
    Route::post('/deudas/import/completo', [DeudaController::class, 'importCompleto'])->name('deudas.import.completo.process');
    Route::get('/deudas/template/completo', [DeudaController::class, 'downloadTemplateCompleto'])->name('deudas.template.completo');
    Route::get('/deudas/import/errors', [DeudaController::class, 'showImportErrors'])->name('deudas.import.errors');
    
    // Rutas para importación selectiva de recibos vencidos
    Route::get('/deudas/import/recibos-vencidos', [DeudaController::class, 'showImportRecibosVencidos'])->name('deudas.import.recibos-vencidos');
    Route::post('/deudas/import/recibos-vencidos', [DeudaController::class, 'importRecibosVencidos'])->name('deudas.import.recibos-vencidos.process');
    
    Route::get('/deudas/{apartamento}', [DeudaController::class, 'show'])->name('deudas.show');
    Route::patch('/deudas/recibo/{recibo}/cambiar-estado', [DeudaController::class, 'cambiarEstadoRecibo'])->name('deudas.cambiar-estado-recibo');
    

    
    // Rutas de Inventario
    Route::resource('inventario', InventarioController::class);
    
    // Rutas de Conciliación
    Route::get('/conciliacion', [ConciliacionController::class, 'index'])->name('conciliacion.index');
    Route::post('/conciliacion/egresos', [ConciliacionController::class, 'store'])->name('conciliacion.store');
    Route::delete('/conciliacion/egresos/{egreso}', [ConciliacionController::class, 'destroy'])->name('conciliacion.destroy');
    
    // Rutas de Recaudación (submenú de Conciliación)
    Route::get('/conciliacion/recaudacion', [ConciliacionController::class, 'recaudacion'])->name('conciliacion.recaudacion');
    
    // Rutas para Espacios
    Route::resource('spaces', SpaceController::class);
    
    // Rutas para Reservas de Espacios
    Route::resource('reservations', ReservationController::class);
    Route::post('/reservations/check-availability', [ReservationController::class, 'checkAvailability'])->name('reservations.check-availability');
    Route::post('/reservations/get-occupied-dates', [ReservationController::class, 'getOccupiedDates'])->name('reservations.get-occupied-dates');
    
    // Rutas para Proveedores
    Route::resource('proveedores', ProveedorController::class);
    
    // Rutas para Egresos
    Route::resource('egresos', EgresoController::class);
    Route::get('/egresos/{egreso}/pdf', [EgresoController::class, 'generatePdf'])->name('egresos.pdf');

    // Rutas para Fondos
    Route::resource('fondos', FondoController::class);
    Route::post('/fondos/{fondo}/ingreso', [FondoController::class, 'registrarIngreso'])->name('fondos.ingreso');
    Route::post('/fondos/{fondo}/egreso', [FondoController::class, 'registrarEgreso'])->name('fondos.egreso');
    Route::delete('/fondos/movimientos/{movimiento}', [FondoController::class, 'eliminarMovimiento'])->name('fondos.movimientos.destroy');
    
    // API endpoints
    Route::get('/api/apartamentos', [ApartamentoController::class, 'getApartamentosApi'])->name('api.apartamentos');
});

// API endpoints con autenticación mejorada para AJAX
Route::middleware('api.auth')->group(function () {
    Route::get('/api/recaudacion/recibos', [ConciliacionController::class, 'getRecibosApi'])->name('api.recaudacion.recibos');
    Route::get('/api/recaudacion/detalle/{recibo}', [ConciliacionController::class, 'detalleRecaudacion'])->name('api.recaudacion.detalle');
});

// API endpoints públicos (sin autenticación para AJAX)
Route::get('/api/recibos-por-apartamento', [PagoController::class, 'getRecibosPorApartamento'])->name('api.recibos-apartamento');
Route::get('/api/saldo-recibo', [PagoController::class, 'getSaldoRecibo'])->name('api.saldo-recibo');

require __DIR__.'/auth.php';