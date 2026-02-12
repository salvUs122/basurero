<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;

class RecorridoController extends Controller
{
    public function show(Recorrido $recorrido)
    {
        $recorrido->load('ruta');
        return view('recorridos.show', compact('recorrido'));
    }

    public function puntos(Recorrido $recorrido)
    {
        return $recorrido->puntos()
            ->orderBy('fecha_gps')
            ->get(['lat','lng','fecha_gps']);
    }

     /**
     * EXPORTAR CSV - FUNCIONAL
     */
        public function exportarCSV(Recorrido $recorrido)
        {
            $puntos = $recorrido->puntos()->orderBy('fecha_gps')->get();
            
            $filename = "recorrido_{$recorrido->id}_" . now()->format('Ymd_His') . ".csv";
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            
            $callback = function() use ($puntos, $recorrido) {
                $file = fopen('php://output', 'w');
                
                // BOM para UTF-8 (Excel)
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Información del recorrido
                fputcsv($file, ['RECORRIDO #' . $recorrido->id]);
                fputcsv($file, ['Fecha', $recorrido->fecha_inicio->format('d/m/Y')]);
                fputcsv($file, ['Conductor', $recorrido->conductor?->name ?? 'N/A']);
                fputcsv($file, ['Camión', $recorrido->camion?->placa ?? 'N/A']);
                fputcsv($file, ['Ruta', $recorrido->ruta?->nombre ?? 'N/A']);
                fputcsv($file, []);
                
                // Cabeceras de columnas
                fputcsv($file, [
                    '#',
                    'Fecha',
                    'Hora',
                    'Latitud',
                    'Longitud',
                    'Precisión (m)',
                    'Velocidad (km/h)'
                ]);
                
                // Datos
                foreach ($puntos as $index => $punto) {
                    fputcsv($file, [
                        $index + 1,
                        $punto->fecha_gps ? date('d/m/Y', strtotime($punto->fecha_gps)) : '',
                        $punto->fecha_gps ? date('H:i:s', strtotime($punto->fecha_gps)) : '',
                        number_format($punto->lat, 6, '.', ''),
                        number_format($punto->lng, 6, '.', ''),
                        $punto->precision_m ?? '',
                        isset($punto->velocidad_mps) ? round($punto->velocidad_mps * 3.6, 1) : ''
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }

}
