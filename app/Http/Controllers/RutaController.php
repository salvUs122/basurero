<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    public function index()
    {
        $rutas = Ruta::orderBy('id', 'desc')->get();
        return view('rutas.index', compact('rutas'));
    }

    public function create()
    {
        return view('rutas.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|max:150',
        'estado' => 'required|in:activa,inactiva',
        'tolerancia_metros' => 'required|integer|min:5|max:500',
        'geometria_geojson' => 'required', // viene del mapa
    ]);

    Ruta::create([
        'nombre' => $request->nombre,
        'estado' => $request->estado,
        'tolerancia_metros' => $request->tolerancia_metros,
        'geometria_geojson' => $request->geometria_geojson,
    ]);

    return redirect()->route('rutas.index')->with('success', 'Ruta creada correctamente');
}

}
