<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';

Route::middleware(['auth', 'role:administrador'])->get('/admin', function () {
    return 'Hola ADMIN ✅';
});

Route::middleware(['auth', 'role:encargado'])->get('/encargado', function () {
    return 'Hola ENCARGADO ✅';
});

Route::middleware(['auth', 'role:conductor'])->get('/conductor', function () {
    return 'Hola CONDUCTOR ✅';
});

use App\Http\Controllers\CamionController;

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/camiones', [CamionController::class, 'index'])->name('camiones.index');
    Route::get('/camiones/crear', [CamionController::class, 'create'])->name('camiones.create');
    Route::post('/camiones', [CamionController::class, 'store'])->name('camiones.store');
});

use App\Http\Controllers\RutaController;

Route::middleware(['auth', 'role:administrador|encargado'])->group(function () {
    Route::get('/rutas', [RutaController::class, 'index'])->name('rutas.index');
    Route::get('/rutas/crear', [RutaController::class, 'create'])->name('rutas.create');
    Route::post('/rutas', [RutaController::class, 'store'])->name('rutas.store');
});
