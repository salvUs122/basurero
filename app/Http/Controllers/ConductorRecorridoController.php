<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use App\Models\Recorrido;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConductorRecorridoController extends Controller
{
    public function pantalla()
    {
        $user = Auth::user();
        
        // Obtener camiones asignados a este conductor
        $camiones = $user->camionesAsignados()
            ->where('estado', 'activo')
            ->orderBy('placa')
            ->get();

        // Obtener rutas disponibles para HOY
        $rutas = collect();
        $diaHoy = strtolower(Carbon::now()->locale('es')->dayName);
        
        foreach ($camiones as $camion) {
            foreach ($camion->rutas as $ruta) {
                $dias = json_decode($ruta->pivot->dias_semana ?? '[]', true);
                if (in_array($diaHoy, $dias) && $ruta->pivot->activa && $ruta->estado == 'activa') {
                    // Agregar información del horario
                    $ruta->horario_hoy = [
                        'inicio' => $ruta->pivot->hora_inicio,
                        'fin' => $ruta->pivot->hora_fin,
                    ];
                    $ruta->camion_asignado = $camion->placa;
                    $rutas->push($ruta);
                }
            }
        }
        
        $rutas = $rutas->unique('id');

        $recorridoActivo = Recorrido::where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->latest('id')
            ->first();

        return view('dashboards.conductor', compact('camiones', 'rutas', 'recorridoActivo'));
    }

    public function iniciar(Request $request)
    {
        $request->validate([
            'camion_id' => 'required|exists:camiones,id',
            'ruta_id' => 'required|exists:rutas,id',
        ]);

        $user = Auth::user();

        // Verificar que el camión pertenezca al conductor
        $camionAsignado = $user->camionesAsignados()
            ->where('id', $request->camion_id)
            ->exists();

        if (!$camionAsignado) {
            return back()->with('error', '❌ No tienes permiso para usar este camión.');
        }

        // Verificar que la ruta esté asignada al camión y activa hoy
        $camion = Camion::find($request->camion_id);
        $rutaAsignada = $camion->rutas()
            ->where('rutas.id', $request->ruta_id)
            ->wherePivot('activa', true)
            ->first();

        if (!$rutaAsignada) {
            return back()->with('error', '❌ Esta ruta no está asignada al camión seleccionado.');
        }

        $diaHoy = strtolower(now()->locale('es')->dayName);
        $dias = json_decode($rutaAsignada->pivot->dias_semana ?? '[]', true);
        
        if (!in_array($diaHoy, $dias)) {
            return back()->with('error', '❌ Esta ruta no está programada para hoy.');
        }

        // Verificar si ya tiene un recorrido activo
        $yaActivo = Recorrido::where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->exists();

        if ($yaActivo) {
            return back()->with('error', '❌ Ya tienes un recorrido en curso.');
        }

        // Verificar si el camión ya está en uso
        $camionEnUso = Recorrido::where('camion_id', $request->camion_id)
            ->where('estado', 'en_curso')
            ->exists();

        if ($camionEnUso) {
            return back()->with('error', '❌ Este camión ya está en uso.');
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

            return back()->with('success', '✅ Recorrido iniciado correctamente. Horario: ' . 
                $rutaAsignada->pivot->hora_inicio . ' - ' . $rutaAsignada->pivot->hora_fin);

        } catch (\Exception $e) {
            Log::error('Error iniciando recorrido: ' . $e->getMessage());
            return back()->with('error', '❌ Error al iniciar recorrido.');
        }
    }

    public function finalizar()
    {
        $recorrido = Recorrido::where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->latest('id')
            ->first();

        if (!$recorrido) {
            return back()->with('error', '❌ No tienes recorrido en curso.');
        }

        try {
            $recorrido->update([
                'estado' => 'finalizado',
                'fecha_fin' => now(),
            ]);

            return back()->with('success', '✅ Recorrido finalizado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error finalizando recorrido: ' . $e->getMessage());
            return back()->with('error', '❌ Error al finalizar recorrido.');
        }
    }
}