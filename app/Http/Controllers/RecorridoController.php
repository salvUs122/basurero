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
}
