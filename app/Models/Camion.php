<?php

namespace App\Models;

use App\Models\Ruta;

use Illuminate\Database\Eloquent\Model;

class Camion extends Model
{
    protected $table = 'camiones';

    protected $fillable = [
        'placa',
        'codigo',
        'estado',
    ];

    public function rutas()
    {
        return $this->belongsToMany(Ruta::class, 'ruta_camion')
            ->withPivot('activa')
            ->withTimestamps();
    }

}


