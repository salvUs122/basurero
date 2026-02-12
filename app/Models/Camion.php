<?php

namespace App\Models;

use App\Models\Ruta;
use App\Models\Recorrido;
use App\Models\HorarioDia; // NUEVO
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Camion extends Model
{
    protected $table = 'camiones';

    protected $fillable = [
        'placa',
        'codigo',
        'estado',
    ];

    /**
     * Relación con Rutas (muchos a muchos)
     */
    public function rutas(): BelongsToMany
    {
        return $this->belongsToMany(Ruta::class, 'ruta_camion')
            ->withPivot(['id', 'activa', 'hora_inicio', 'hora_fin', 'dias_semana'])
            ->withTimestamps();
    }

    /**
     * Relación con Recorridos (uno a muchos)
     */
    public function recorridos(): HasMany
    {
        return $this->hasMany(Recorrido::class);
    }

    /**
     * Helper para obtener horarios por día USANDO LA NUEVA TABLA
     */
    public function getHorariosPorDia($rutaId): array
    {
        $asignacion = $this->rutas()->where('rutas.id', $rutaId)->first();
        
        if (!$asignacion) {
            return [];
        }

        // Obtener horarios de la nueva tabla
        $horarios = HorarioDia::where('ruta_camion_id', $asignacion->pivot->id)
            ->get()
            ->keyBy('dia')
            ->toArray();

        return $horarios;
    }

    /**
     * Helper para obtener el horario de un día específico
     */
    public function getHorarioDia($rutaId, $dia): ?array
    {
        $asignacion = $this->rutas()->where('rutas.id', $rutaId)->first();
        
        if (!$asignacion) {
            return null;
        }

        $horario = HorarioDia::where('ruta_camion_id', $asignacion->pivot->id)
            ->where('dia', $dia)
            ->first();

        if ($horario) {
            return [
                'hora_inicio' => $horario->hora_inicio,
                'hora_fin' => $horario->hora_fin,
                'activo' => $horario->activo
            ];
        }

        return null;
    }
}