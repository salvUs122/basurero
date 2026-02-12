<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use App\Models\Recorrido;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ConductorRecorridoController extends Controller
{
    public function pantalla()
    {
        $camiones = Camion::where('estado', 'activo')->orderBy('placa')->get();
        $rutas = Ruta::where('estado', 'activa')->orderBy('nombre')->get();

        $recorridoActivo = Recorrido::where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->latest('id')
            ->first();


        return view('dashboards.conductor', compact('camiones', 'rutas', 'recorridoActivo'));
    }

   // Reemplaza el método iniciar() con este código:

public function iniciar(Request $request)
{
    $request->validate([
        'camion_id' => 'required|exists:camiones,id',
        'ruta_id' => 'required|exists:rutas,id',
    ]);

    // Verificar si el camión ya está en uso
    $camionEnUso = Recorrido::where('camion_id', $request->camion_id)
        ->where('estado', 'en_curso')
        ->exists();

    if ($camionEnUso) {
        return back()->with('error', 'Este camión ya está en uso en otro recorrido.');
    }

    // Evitar 2 recorridos activos para el mismo conductor
    $yaActivo = Recorrido::where('conductor_id', Auth::id())
        ->where('estado', 'en_curso')
        ->exists();

    if ($yaActivo) {
        return back()->with('error', 'Ya tienes un recorrido en curso.');
    }

    try {
        $recorrido = Recorrido::create([
            'camion_id' => $request->camion_id,
            'ruta_id' => $request->ruta_id,
            'conductor_id' => Auth::id(),
            'estado' => 'en_curso',
            'fecha_inicio' => now(),
            'total_puntos' => 0,
            'eventos_fuera_ruta' => 0,
        ]);

        // Registrar punto inicial si es posible obtener ubicación
        return back()->with('success', 'Recorrido iniciado correctamente. ID: ' . $recorrido->id);

    } catch (\Exception $e) {
        return back()->with('error', 'Error al iniciar recorrido: ' . $e->getMessage());
    }
}

    public function finalizar()
    {
       $recorrido = Recorrido::where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->latest('id')
            ->first();


        if (!$recorrido) {
            return back()->with('error', 'No tienes recorrido en curso.');
        }

        $recorrido->update([
            'estado' => 'finalizado',
            'fecha_fin' => now(),
        ]);

        return back()->with('success', 'Recorrido finalizado.');
    }
}
