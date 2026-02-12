<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\RecorridoController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\RecorridosAdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AsignacionRutaCamionController;
use App\Http\Controllers\ConductorRecorridoController;
use App\Http\Controllers\ConductorGpsController;
use App\Models\Camion; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirección inicial
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard principal (redirige según rol)
Route::get('/dashboard', function () {
    /** @var \App\Models\User $u */
    $u = Auth::user();

    if ($u->hasRole('administrador')) return redirect()->route('admin.dashboard');
    if ($u->hasRole('encargado'))     return redirect()->route('encargado.dashboard');
    if ($u->hasRole('conductor'))     return redirect()->route('conductor.dashboard');

    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de perfil (para todos los usuarios autenticados)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// RUTAS PARA ADMINISTRADOR
// ============================================
Route::middleware(['auth', 'role:administrador'])->group(function () {
    // Dashboard administrativo
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Gestión de camiones
    Route::get('/camiones', [CamionController::class, 'index'])->name('camiones.index');
    Route::get('/camiones/crear', [CamionController::class, 'create'])->name('camiones.create');
    Route::post('/camiones', [CamionController::class, 'store'])->name('camiones.store');
    
    // Asignación de rutas a camiones
    Route::get('/camiones/{camion}/asignar-rutas', [AsignacionRutaCamionController::class, 'edit'])
        ->name('camiones.asignar_rutas');
    Route::post('/camiones/{camion}/asignar-rutas', [AsignacionRutaCamionController::class, 'update'])
        ->name('camiones.guardar_rutas');
});

    // Editar y eliminar camiones
    Route::get('/camiones/{camion}/editar', [CamionController::class, 'edit'])->name('camiones.edit');
    Route::put('/camiones/{camion}', [CamionController::class, 'update'])->name('camiones.update');
    Route::delete('/camiones/{camion}', [CamionController::class, 'destroy'])->name('camiones.destroy');

    // Obtener datos de camión para AJAX
    Route::get('/camiones/{camion}/datos', [CamionController::class, 'getCamion'])->name('camiones.datos');

    // Eliminar asignación específica
    Route::delete('/camiones/{camion}/eliminar-ruta', [AsignacionRutaCamionController::class, 'destroyAsignacion'])
        ->name('camiones.eliminar_ruta');

    // Actualizar horarios por día
    Route::post('/camiones/{camion}/horarios', [AsignacionRutaCamionController::class, 'updateHorariosPorDia'])
        ->name('camiones.horarios');

// ============================================
// RUTAS PARA ADMINISTRADOR Y ENCARGADO
// ============================================
Route::middleware(['auth', 'role:administrador|encargado'])->group(function () {
    // Gestión de rutas
    Route::get('/rutas', [RutaController::class, 'index'])->name('rutas.index');
    Route::get('/rutas/crear', [RutaController::class, 'create'])->name('rutas.create');
    Route::post('/rutas', [RutaController::class, 'store'])->name('rutas.store');
    
    // Monitoreo en vivo
    Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');
    Route::get('/monitoreo/{recorrido}/puntos', [MonitoreoController::class, 'puntos'])->name('monitoreo.puntos');
    Route::get('/monitoreo/{recorrido}/eventos', [MonitoreoController::class, 'eventos'])->name('monitoreo.eventos');
    
    // PUNTOS ACTIVOS EN TIEMPO REAL (RUTA NUEVA - DEBE ESTAR AQUÍ)
    Route::get('/monitoreo/puntos-activos', [MonitoreoController::class, 'puntosActivos'])
        ->name('monitoreo.puntos_activos');
    
    // Historial de recorridos
    Route::get('/recorridos', [RecorridosAdminController::class, 'index'])->name('recorridos.index');
    Route::get('/recorridos/{recorrido}', [RecorridosAdminController::class, 'show'])->name('recorridos.show');
    
    // Detalles de recorridos individuales
    Route::get('/recorridos/{recorrido}/detalle', [RecorridoController::class, 'show'])->name('recorridos.show');
    Route::get('/recorridos/{recorrido}/puntos', [RecorridoController::class, 'puntos'])->name('recorridos.puntos');
    });

    // Detalles del camión (modal) - Esto también debe estar en el grupo correcto
    Route::get('/camiones/{camion}/detalles', function(Camion $camion) {
        $html = view('camiones.partials.detalles', compact('camion'))->render();
        return response()->json(['html' => $html]);
    })->middleware(['auth', 'role:administrador|encargado']);

    // AGREGAR ESTA RUTA DENTRO DEL GRUPO DE ADMINISTRADOR
    Route::post('/camiones/{camion}/guardar-horarios-dia', [AsignacionRutaCamionController::class, 'guardarHorariosPorDia'])
        ->name('camiones.guardar_horarios_dia');
// ============================================
// RUTAS PARA ENCARGADO
// ============================================
Route::middleware(['auth', 'role:encargado'])->group(function () {
    // Dashboard del encargado (redirige al dashboard administrativo)
    Route::get('/encargado', function () {
        return redirect()->route('admin.dashboard');
    })->name('encargado.dashboard');
});

// ============================================
// RUTAS PARA CONDUCTOR
// ============================================
Route::middleware(['auth', 'role:conductor'])->group(function () {
    // Dashboard del conductor
    Route::get('/conductor', function () {
        return redirect()->route('conductor.recorrido');
    })->name('conductor.dashboard');
    
    // Gestión de recorridos
    Route::get('/conductor/recorrido', [ConductorRecorridoController::class, 'pantalla'])
        ->name('conductor.recorrido');
    Route::post('/conductor/recorrido/iniciar', [ConductorRecorridoController::class, 'iniciar'])
        ->name('conductor.recorrido.iniciar');
    Route::post('/conductor/recorrido/finalizar', [ConductorRecorridoController::class, 'finalizar'])
        ->name('conductor.recorrido.finalizar');
    
    // Envío de GPS
    Route::post('/conductor/gps', [ConductorGpsController::class, 'guardar'])
        ->name('conductor.gps.guardar');
});

// ============================================
// ARCHIVOS DE AUTENTICACIÓN
// ============================================
require __DIR__.'/auth.php';