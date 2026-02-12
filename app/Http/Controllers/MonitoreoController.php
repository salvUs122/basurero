<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use App\Models\PuntoRecorrido;
use App\Models\EventoRecorrido;
use Illuminate\Http\Request;

class MonitoreoController extends Controller
{
    public function index()
    {
        $recorridosActivos = Recorrido::with(['ruta', 'camion', 'conductor'])
            ->where('estado', 'en_curso')
            ->latest('id')
            ->get();

        $recorridosJs = $recorridosActivos->map(function ($r) {
            return [
                'id' => $r->id,
                'ruta_geojson' => optional($r->ruta)->geometria_geojson,
                'tolerancia' => optional($r->ruta)->tolerancia_metros,
            ];
        })->values();

        return view('monitoreo.index', compact('recorridosActivos', 'recorridosJs'));
    }


    public function puntos(Recorrido $recorrido)
    {
        // Devuelve puntos del recorrido en orden
        $puntos = PuntoRecorrido::where('recorrido_id', $recorrido->id)
            ->orderBy('fecha_gps')
            ->get();

        return response()->json($puntos);
    }

    public function eventos(Recorrido $recorrido)
    {
        $eventos = EventoRecorrido::where('recorrido_id', $recorrido->id)
            ->orderBy('id', 'desc')
            ->take(50)
            ->get(['id','tipo','mensaje','lat','lng','distancia_m','fecha_evento']);

        return response()->json($eventos);
    }

    // Agrega este método a MonitoreoController:

    public function puntosActivos()
    {
        // Obtener todos los recorridos activos
        $recorridosActivos = Recorrido::with(['ruta', 'camion', 'conductor'])
            ->where('estado', 'en_curso')
            ->get();

        $puntosPorRecorrido = [];
        
        foreach ($recorridosActivos as $recorrido) {
            // Obtener últimos 100 puntos del recorrido (más para ver la ruta completa)
            $puntos = PuntoRecorrido::where('recorrido_id', $recorrido->id)
                ->orderBy('fecha_gps', 'asc') // Cambiado a ASC para orden cronológico
                ->take(100)
                ->get(['lat', 'lng', 'fecha_gps', 'velocidad_mps']);
            
            // Obtener ruta planificada completa
            $rutaGeojson = null;
            $rutaCoords = [];
            
            if ($recorrido->ruta && $recorrido->ruta->geometria_geojson) {
                $rutaGeojson = $recorrido->ruta->geometria_geojson;
                
                // Parsear coordenadas para fácil acceso
                try {
                    $geo = json_decode($rutaGeojson, true);
                    if (isset($geo['coordinates'])) {
                        $rutaCoords = $geo['coordinates'];
                    }
                } catch (\Exception $e) {
                    // Ignorar error
                }
            }
            
            $puntosPorRecorrido[] = [
                'recorrido_id' => $recorrido->id,
                'camion' => $recorrido->camion->placa ?? 'N/A',
                'conductor' => $recorrido->conductor->name ?? 'N/A',
                'ruta' => $recorrido->ruta->nombre ?? 'Sin ruta',
                'ruta_geojson' => $rutaGeojson,
                'ruta_coords' => $rutaCoords, // Nuevo: coordenadas parseadas
                'tolerancia' => $recorrido->ruta->tolerancia_metros ?? 50,
                'puntos' => $puntos,
                'ultimo_punto' => $puntos->last(), // Cambiado a last() porque ahora es ASC
                'total_puntos' => $recorrido->total_puntos,
                'eventos_fuera_ruta' => $recorrido->eventos_fuera_ruta
            ];
        }

        return response()->json($puntosPorRecorrido);
    }
    }

