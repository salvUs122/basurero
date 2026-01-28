<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use Illuminate\Http\Request;

class CamionController extends Controller
{
    public function index()
    {
        $camiones = Camion::all();
        return view('camiones.index', compact('camiones'));
    }

    public function create()
    {
        return view('camiones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|unique:camiones',
            'codigo' => 'required|unique:camiones',
            'estado' => 'required',
        ]);

        Camion::create($request->all());

        return redirect()->route('camiones.index')
            ->with('success', 'Camión creado correctamente');
    }
}
