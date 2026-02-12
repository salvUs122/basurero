<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Rutas</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .hover-lift:hover { transform: translateY(-3px); transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Barra de navegación -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-route text-blue-600 text-xl mr-2"></i>
                        <span class="font-bold text-gray-800">Gestión de Rutas</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Rutas</p>
                            <p class="text-3xl font-bold mt-2">{{ $rutas->count() }}</p>
                        </div>
                        <div class="bg-blue-400 p-3 rounded-full">
                            <i class="fas fa-route text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Rutas Activas</p>
                            <p class="text-3xl font-bold mt-2">{{ $rutas->where('estado', 'activa')->count() }}</p>
                        </div>
                        <div class="bg-green-400 p-3 rounded-full">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Con Camiones</p>
                            <p class="text-3xl font-bold mt-2">{{ $rutas->filter(fn($r) => $r->camiones->count() > 0)->count() }}</p>
                        </div>
                        <div class="bg-purple-400 p-3 rounded-full">
                            <i class="fas fa-truck text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Encabezado y botón -->
            <div class="mb-6 flex justify-between items-center">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">Lista de Rutas</h3>
                    <p class="text-sm text-gray-600">Gestiona todas las rutas del sistema</p>
                </div>
                <a href="{{ route('rutas.create') }}" 
                   class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 
                          text-white font-medium py-2 px-4 rounded-lg transition-all duration-300 
                          flex items-center shadow-lg hover:shadow-xl">
                    <i class="fas fa-plus mr-2"></i>
                    Nueva Ruta
                </a>
            </div>

            <!-- Tabla de rutas -->
            <div class="bg-white rounded-xl card-shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Rutas Registradas</h3>
                            <p class="text-sm text-gray-600">{{ $rutas->count() }} rutas en el sistema</p>
                        </div>
                    </div>
                </div>
                
                @if($rutas->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Información</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Camiones Asignados</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Configuración</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rutas as $ruta)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <!-- Información de la ruta -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-route text-blue-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $ruta->nombre }}</div>
                                                <div class="mt-1">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                          {{ $ruta->estado == 'activa' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ ucfirst($ruta->estado) }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    Creada: {{ $ruta->created_at->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Camiones asignados -->
                                    <td class="px-6 py-4">
                                        @if($ruta->camiones->count() > 0)
                                            <div class="space-y-1">
                                                @foreach($ruta->camiones->take(3) as $camion)
                                                    <div class="text-sm text-gray-900">
                                                        <i class="fas fa-truck text-gray-400 mr-1"></i>
                                                        {{ $camion->placa }}
                                                        <span class="text-xs {{ $camion->pivot->activa ? 'text-green-600' : 'text-red-600' }}">
                                                            ({{ $camion->pivot->activa ? 'Activa' : 'Inactiva' }})
                                                        </span>
                                                    </div>
                                                @endforeach
                                                @if($ruta->camiones->count() > 3)
                                                    <div class="text-xs text-gray-500">
                                                        +{{ $ruta->camiones->count() - 3 }} más
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 italic">Sin camiones asignados</span>
                                        @endif
                                    </td>

                                    <!-- Configuración -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <i class="fas fa-ruler text-gray-400 mr-2"></i>
                                                Tolerancia: {{ $ruta->tolerancia_metros }}m
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $ruta->geometria_geojson ? 'Ruta definida' : 'Sin geometría' }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <div class="text-gray-300 mb-4">
                        <i class="fas fa-route text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">No hay rutas registradas</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza creando tu primera ruta.</p>
                    <a href="{{ route('rutas.create') }}" 
                       class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white 
                              rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Crear primera ruta
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para ver ruta (opcional, puedes quitarlo si no lo necesitas) -->
    <div id="modal-ruta" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-route mr-2 text-blue-600"></i>
                    Vista Previa de Ruta
                </h3>
                <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modal-content" class="mb-4">
                <!-- Contenido cargado por JavaScript -->
            </div>
            <div class="flex justify-end">
                <button onclick="cerrarModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
    // Función para ver detalles de la ruta (opcional)
    function verRuta(rutaId) {
        // Mostrar loading
        document.getElementById('modal-content').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-blue-500 text-3xl mb-3"></i>
                <p class="text-gray-600">Cargando información de la ruta...</p>
            </div>
        `;
        
        // Mostrar modal
        document.getElementById('modal-ruta').classList.remove('hidden');
        
        setTimeout(() => {
            document.getElementById('modal-content').innerHTML = `
                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Información de la ruta ID: ${rutaId}</p>
                        <p class="text-sm text-gray-500">Para ver el mapa completo, asigna la ruta a un camión y monitorea el recorrido.</p>
                    </div>
                </div>
            `;
        }, 500);
    }
    
    // Función para cerrar modal
    function cerrarModal() {
        document.getElementById('modal-ruta').classList.add('hidden');
    }
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('modal-ruta').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });
    </script>
</body>
</html>