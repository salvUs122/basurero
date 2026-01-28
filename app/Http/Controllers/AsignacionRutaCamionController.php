<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use App\Models\Ruta;
use Illuminate\Http\Request;

class AsignacionRutaCamionController extends Controller
{
    public function edit(Camion $camion)
    {
        $rutas = Ruta::orderBy('nombre')->get();
        $asignadas = $camion->rutas()->pluck('rutas.id')->toArray();

        return view('camiones.asignar_rutas', compact('camion', 'rutas', 'asignadas'));
    }

    public function update(Request $request, Camion $camion)
    {
        $request->validate([
            'rutas' => 'array',
            'rutas.*' => 'integer',
        ]);

        $ids = $request->input('rutas', []);
        $camion->rutas()->sync($ids);

        return redirect()->route('camiones.index')->with('success', 'Rutas asignadas correctamente');
    }
}
