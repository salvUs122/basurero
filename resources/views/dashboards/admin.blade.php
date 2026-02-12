<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    
    <!-- Tailwind CSS desde CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Estilos adicionales */
        .gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .gradient-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .gradient-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .gradient-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .hover-lift:hover { transform: translateY(-5px); transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Barra de navegación simplificada -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-truck text-blue-600 text-xl mr-2"></i>
                        <span class="font-bold text-gray-800">Sistema de Camiones</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="text-gray-600 hover:text-gray-900 px-3 py-2">
                        <i class="fas fa-sign-out-alt mr-1"></i> Salir
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-truck-moving text-blue-600 mr-2"></i>
                            Panel de Control Administrativo
                        </h1>
                        <p class="text-gray-600 mt-1">Sistema de monitoreo de flota de camiones recolectores</p>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                        <span class="text-sm text-gray-500">Fecha:</span>
                        <span class="ml-2 font-medium">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Camiones Activos -->
                <div class="bg-white rounded-xl card-shadow overflow-hidden hover-lift">
                    <div class="gradient-blue p-6 text-white">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">CAMIONES ACTIVOS</p>
                                <p class="text-3xl font-bold mt-2">{{ $camionesActivos }} <span class="text-blue-200 text-lg">/ {{ $totalCamiones }}</span></p>
                            </div>
                            <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                                <i class="fas fa-truck text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-blue-400 bg-opacity-30 rounded-full h-2">
                                <div class="bg-white h-2 rounded-full" 
                                     style="width: {{ $totalCamiones > 0 ? ($camionesActivos/$totalCamiones)*100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recorridos Activos -->
                <div class="bg-white rounded-xl card-shadow overflow-hidden hover-lift">
                    <div class="gradient-green p-6 text-white">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-green-100 text-sm font-medium">RECORRIDOS ACTIVOS</p>
                                <p class="text-3xl font-bold mt-2">{{ $recorridosActivosCount }}</p>
                                <p class="text-green-100 text-xs mt-1">
                                    <i class="fas fa-calendar-day mr-1"></i> {{ $recorridosHoy }} hoy
                                </p>
                            </div>
                            <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                                <i class="fas fa-route text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conductores -->
                <div class="bg-white rounded-xl card-shadow overflow-hidden hover-lift">
                    <div class="gradient-purple p-6 text-white">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">CONDUCTORES</p>
                                <p class="text-3xl font-bold mt-2">{{ $totalConductores }}</p>
                                <p class="text-purple-100 text-xs mt-1">
                                    <i class="fas fa-user-check mr-1"></i> Registrados
                                </p>
                            </div>
                            <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas Activas -->
                <div class="bg-white rounded-xl card-shadow overflow-hidden hover-lift">
                    <div class="gradient-orange p-6 text-white">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">ALERTAS ACTIVAS</p>
                                <p class="text-3xl font-bold mt-2">{{ $alertasActivas }}</p>
                                <p class="text-orange-100 text-xs mt-1">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Requieren atención
                                </p>
                            </div>
                            <div class="bg-orange-400 bg-opacity-30 p-3 rounded-full">
                                <i class="fas fa-bell text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de recorridos activos -->
            <div class="bg-white rounded-xl card-shadow overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-satellite-dish text-blue-600 mr-2"></i>
                                Recorridos en Tiempo Real
                            </h2>
                            <p class="text-sm text-gray-600">Monitoreo activo de la flota</p>
                        </div>
                        <a href="{{ route('monitoreo.index') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Ver monitoreo completo
                        </a>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    @if($recorridosEnCurso->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Camión</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruta</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conductor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Actualización</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recorridosEnCurso as $recorrido)
                                @php
                                    $estado = $estados[$recorrido->id] ?? ['label' => 'DESCONOCIDO', 'color' => '#6b7280'];
                                    $punto = $ultimos[$recorrido->id] ?? null;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-truck text-blue-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $recorrido->camion->placa ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500">{{ $recorrido->camion->codigo ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $recorrido->ruta->nombre ?? 'Sin ruta' }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $recorrido->ruta_id ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <i class="fas fa-user-circle text-gray-400 mr-1"></i>
                                            {{ $recorrido->conductor->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">ID: {{ $recorrido->conductor_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($punto)
                                            <div class="text-sm font-medium text-gray-900">
                                                <i class="fas fa-clock text-gray-400 mr-1"></i>
                                                {{ $punto->fecha_gps->diffForHumans() }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $punto->fecha_gps->format('H:i:s') }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 italic">Sin datos</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              style="background-color: {{ $estado['color'] }}20; color: {{ $estado['color'] }};">
                                            <i class="fas fa-circle mr-1" style="color: {{ $estado['color'] }}"></i>
                                            {{ $estado['label'] }}
                                        </span>
                                        @if($punto && $punto->velocidad_mps)
                                            <div class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-tachometer-alt mr-1"></i>
                                                {{ round($punto->velocidad_mps * 3.6, 1) }} km/h
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('monitoreo.index') }}?recorrido={{ $recorrido->id }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-4 inline-flex items-center">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-12">
                        <div class="text-gray-300 text-5xl mb-4">
                            <i class="fas fa-road"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700">No hay recorridos activos</h3>
                        <p class="mt-1 text-sm text-gray-500">Todos los camiones están fuera de servicio o en mantenimiento.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Enlaces rápidos -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <a href="{{ route('monitoreo.index') }}" 
                   class="bg-white rounded-xl card-shadow p-6 hover-lift border border-transparent hover:border-blue-200">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-map-marked-alt text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Monitoreo en Vivo</h3>
                            <p class="text-sm text-gray-600">Seguimiento GPS</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('camiones.index') }}" 
                   class="bg-white rounded-xl card-shadow p-6 hover-lift border border-transparent hover:border-green-200">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-truck-loading text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Gestión de Camiones</h3>
                            <p class="text-sm text-gray-600">Administrar flota</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('rutas.index') }}" 
                   class="bg-white rounded-xl card-shadow p-6 hover-lift border border-transparent hover:border-purple-200">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-route text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Rutas</h3>
                            <p class="text-sm text-gray-600">Configurar trayectos</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('recorridos.index') }}" 
                   class="bg-white rounded-xl card-shadow p-6 hover-lift border border-transparent hover:border-orange-200">
                    <div class="flex items-center">
                        <div class="bg-orange-100 p-3 rounded-lg">
                            <i class="fas fa-chart-bar text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Reportes</h3>
                            <p class="text-sm text-gray-600">Historial y análisis</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <div>
                        <i class="fas fa-copyright mr-1"></i> Sistema de Camiones {{ date('Y') }}
                    </div>
                    <div>
                        <span class="inline-flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Sistema operativo
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para el efecto hover
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.hover-lift');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transition = 'transform 0.3s ease';
                });
            });
        });
    </script>
</body>
</html>