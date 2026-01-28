<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $table = 'rutas';

    protected $fillable = [
        'nombre',
        'estado',
        'tolerancia_metros',
        'geometria_geojson',
    ];
}
