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
        ]);

        // Por ahora, como todavía no dibujamos en mapa, guardamos una ruta "vacía" mínima.
        $geojson_minimo = '{"type":"LineString","coordinates":[[-63.1800,-17.7800],[-63.1810,-17.7810]]}';

        Ruta::create([
            'nombre' => $request->nombre,
            'estado' => $request->estado,
            'tolerancia_metros' => $request->tolerancia_metros,
            'geometria_geojson' => $geojson_minimo,
        ]);

        return redirect()->route('rutas.index')->with('success', 'Ruta creada correctamente');
    }
}
