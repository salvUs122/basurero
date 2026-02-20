<?php

namespace App\Http\Controllers;

use App\Models\PuntoRecorrido;
use App\Models\Recorrido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // AÑADE ESTA LÍNEA
use App\Models\EventoRecorrido;
use App\Support\Geo;
use Illuminate\Support\Facades\DB;

class ConductorGpsController extends Controller
{
    public function guardar(Request $request)
    {
        // Log para depuración
        Log::info('GPS recibido:', $request->all());
        
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'precision_m' => 'nullable|numeric',
            'velocidad_mps' => 'nullable|numeric',
            'rumbo_grados' => 'nullable|numeric',
            'fecha_gps' => 'required|date',
        ]);

        // Buscar recorrido activo del conductor
        $recorrido = Recorrido::with('ruta')
            ->where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->latest('id')
            ->first();

        if (!$recorrido) {
            Log::error('No hay recorrido en curso para el usuario: ' . Auth::id());
            return response()->json([
                'ok' => false, 
                'mensaje' => 'No hay recorrido en curso. Inicia un recorrido primero.',
                'recorrido_id' => null
            ], 409);
        }

        Log::info('Recorrido encontrado:', ['id' => $recorrido->id]);

        try {
            // Crear punto de recorrido
            $punto = PuntoRecorrido::create([
                'recorrido_id' => $recorrido->id,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'precision_m' => $request->precision_m,
                'velocidad_mps' => $request->velocidad_mps,
                'rumbo_grados' => $request->rumbo_grados,
                'fecha_gps' => $request->fecha_gps,
            ]);

            Log::info('Punto guardado:', ['punto_id' => $punto->id]);

            // Detectar fuera de ruta
            if ($recorrido->ruta && $recorrido->ruta->geometria_geojson) {
                $geo = json_decode($recorrido->ruta->geometria_geojson, true);
                $coords = $geo['coordinates'] ?? [];

                if (is_array($coords) && count($coords) >= 2) {
                    $line = array_map(fn($c) => [ (float)$c[1], (float)$c[0] ], $coords);
                    
                    $dist = Geo::pointToPolylineMeters((float)$request->lat, (float)$request->lng, $line);
                    $tol = (int)($recorrido->ruta->tolerancia_metros ?? 50);

                    // Anti-spam: 1 evento cada 30s
                    $ultimo = EventoRecorrido::where('recorrido_id', $recorrido->id)
                        ->where('tipo', 'fuera_ruta')
                        ->latest('id')
                        ->first();

                    $puedeRegistrar = true;
                    if ($ultimo && $ultimo->fecha_evento) {
                        $puedeRegistrar = $ultimo->fecha_evento->diffInSeconds(now()) >= 30;
                    }

                    if ($dist > $tol && $puedeRegistrar) {
                        EventoRecorrido::create([
                            'recorrido_id' => $recorrido->id,
                            'tipo' => 'fuera_ruta',
                            'mensaje' => "Se salió de la ruta (dist: " . round($dist) . " m)",
                            'lat' => $request->lat,
                            'lng' => $request->lng,
                            'distancia_m' => (int) round($dist),
                            'fecha_evento' => $request->fecha_gps,
                        ]);

                        $recorrido->increment('eventos_fuera_ruta');
                        Log::info('Evento fuera de ruta registrado:', ['distancia' => $dist]);
                    }
                }
            }

            // Incrementar contador de puntos
            $recorrido->increment('total_puntos');

            return response()->json([
                'ok' => true,
                'recorrido_id' => $recorrido->id,
                'punto_id' => $punto->id,
                'total_puntos' => $recorrido->total_puntos
            ]);

        } catch (\Exception $e) {
            Log::error('Error guardando punto GPS:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

        /**
     * Guardar múltiples puntos GPS (para worker)
     */
    public function guardarMultiples(Request $request)
    {
        $request->validate([
            'puntos' => 'required|array|min:1',
            'puntos.*.lat' => 'required|numeric',
            'puntos.*.lng' => 'required|numeric',
            'puntos.*.precision_m' => 'nullable|numeric',
            'puntos.*.velocidad_mps' => 'nullable|numeric',
            'puntos.*.rumbo_grados' => 'nullable|numeric',
            'puntos.*.fecha_gps' => 'required|date',
            'recorrido_id' => 'nullable|exists:recorridos,id'
        ]);

        // Buscar recorrido activo
        $recorrido = Recorrido::with('ruta')
            ->where('conductor_id', Auth::id())
            ->where('estado', 'en_curso')
            ->latest('id')
            ->first();

        if (!$recorrido) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No hay recorrido en curso'
            ], 409);
        }

        $puntosGuardados = 0;
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($request->puntos as $punto) {
                // Crear punto
                PuntoRecorrido::create([
                    'recorrido_id' => $recorrido->id,
                    'lat' => $punto['lat'],
                    'lng' => $punto['lng'],
                    'precision_m' => $punto['precision_m'] ?? null,
                    'velocidad_mps' => $punto['velocidad_mps'] ?? null,
                    'rumbo_grados' => $punto['rumbo_grados'] ?? null,
                    'fecha_gps' => $punto['fecha_gps'],
                ]);
                
                $puntosGuardados++;
                
                // Detectar fuera de ruta (solo cada 3 puntos para no sobrecargar)
                if ($puntosGuardados % 3 === 0 && $recorrido->ruta) {
                    $this->detectarFueraDeRuta($recorrido, $punto);
                }
            }
            
            // Actualizar contador
            $recorrido->increment('total_puntos', $puntosGuardados);
            
            DB::commit();
            
            return response()->json([
                'ok' => true,
                'guardados' => $puntosGuardados,
                'total' => $recorrido->total_puntos
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando múltiples puntos: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error interno',
                'guardados' => $puntosGuardados
            ], 500);
        }
    }

    /**
     * Método auxiliar para detectar fuera de ruta
     */
    private function detectarFueraDeRuta($recorrido, $punto)
    {
        if (!$recorrido->ruta || !$recorrido->ruta->geometria_geojson) {
            return;
        }
        
        $geo = json_decode($recorrido->ruta->geometria_geojson, true);
        $coords = $geo['coordinates'] ?? [];
        
        if (!is_array($coords) || count($coords) < 2) {
            return;
        }
        
        $line = array_map(fn($c) => [(float)$c[1], (float)$c[0]], $coords);
        $dist = Geo::pointToPolylineMeters((float)$punto['lat'], (float)$punto['lng'], $line);
        $tol = (int)($recorrido->ruta->tolerancia_metros ?? 50);
        
        if ($dist <= $tol) {
            return;
        }
        
        // Anti-spam: 1 evento cada 30 segundos
        $ultimo = EventoRecorrido::where('recorrido_id', $recorrido->id)
            ->where('tipo', 'fuera_ruta')
            ->latest('id')
            ->first();
        
        if ($ultimo && $ultimo->fecha_evento->diffInSeconds(now()) < 30) {
            return;
        }
        
        EventoRecorrido::create([
            'recorrido_id' => $recorrido->id,
            'tipo' => 'fuera_ruta',
            'mensaje' => "Se salió de la ruta (dist: " . round($dist) . " m)",
            'lat' => $punto['lat'],
            'lng' => $punto['lng'],
            'distancia_m' => (int) round($dist),
            'fecha_evento' => $punto['fecha_gps'],
        ]);
        
        $recorrido->increment('eventos_fuera_ruta');
    }
}