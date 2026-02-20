<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'licencia',
        'direccion',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con recorridos (como conductor)
     */
    public function recorridos()
    {
        return $this->hasMany(Recorrido::class, 'conductor_id');
    }

    /**
     * Relación con camiones asignados
     */
    public function camionesAsignados()
    {
        return $this->hasMany(Camion::class, 'conductor_id');
    }

    /**
     * Obtener los camiones disponibles para este conductor
     */
    public function getCamionesDisponibles()
    {
        if ($this->hasRole('administrador')) {
            return Camion::where('estado', 'activo')->get();
        }
        
        return $this->camionesAsignados()
            ->where('estado', 'activo')
            ->get();
    }

    /**
     * Obtener las rutas disponibles para HOY según sus camiones
     */
    public function getRutasDisponiblesHoy()
    {
        $rutas = collect();
        $diaHoy = strtolower(now()->locale('es')->dayName);
        
        foreach ($this->camionesAsignados as $camion) {
            foreach ($camion->rutas as $ruta) {
                $dias = json_decode($ruta->pivot->dias_semana ?? '[]', true);
                if (in_array($diaHoy, $dias) && $ruta->pivot->activa) {
                    $rutas->push($ruta);
                }
            }
        }
        
        return $rutas->unique('id');
    }
    /**
 * Relación con camiones asignados (como conductor)
 */
   
}