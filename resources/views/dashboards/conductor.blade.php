<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Conductor</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
     <!-- 🔴 NUEVO: PWA / Instalación en móvil -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Conductor GPS">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <!-- Para Android -->
    <meta name="theme-color" content="#3b82f6">
    <style>
        .gradient-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .hover-lift:hover { transform: translateY(-3px); transition: transform 0.3s ease; }
        .pulse-animation { animation: pulse 2s infinite; }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .gps-active {
            animation: pulse 1.5s infinite;
        }
        
        #map { 
            border-radius: 12px;
            border: 2px solid #e5e7eb;
        }
        
        .leaflet-control { z-index: 1000 !important; }
        
        .ruta-planificada {
            animation: pulse-line 2s infinite;
        }
        
        @keyframes pulse-line {
            0% { opacity: 0.6; }
            50% { opacity: 0.9; }
            100% { opacity: 0.6; }
        }
        
        .custom-marker-conductor {
            z-index: 1000 !important;
        }
        
        .leaflet-popup-content {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-width: 200px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Barra de navegación -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="bg-blue-600 p-2 rounded-lg mr-3">
                            <i class="fas fa-user-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <span class="font-bold text-gray-800">Panel del Conductor</span>
                            <p class="text-xs text-gray-600">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                        <i class="far fa-clock mr-1"></i>
                        <span id="current-time">{{ now()->format('H:i') }}</span>
                    </div>
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="text-gray-600 hover:text-red-600 px-3 py-2 rounded-lg hover:bg-red-50 transition-colors">
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
                            <i class="fas fa-road text-blue-600 mr-2"></i>
                            Gestión de Recorrido
                        </h1>
                        <p class="text-gray-600 mt-1">Inicia y monitorea tu recorrido en tiempo real</p>
                    </div>
                    <div class="flex space-x-2">
                        <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                            <span class="text-sm text-gray-500">Fecha:</span>
                            <span class="ml-2 font-medium">{{ now()->format('d/m/Y') }}</span>
                        </div>
                        @if($recorridoActivo)
                        <div class="bg-green-100 px-4 py-2 rounded-lg border border-green-200">
                            <span class="text-sm text-green-700 font-medium">
                                <i class="fas fa-check-circle mr-1"></i> Recorrido activo
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-green-800 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-red-800 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna izquierda -->
                <div class="lg:col-span-1 space-y-6">
                    @if($recorridoActivo)
                        <!-- Tarjeta de recorrido activo -->
                        <div class="bg-white rounded-xl card-shadow overflow-hidden">
                            <div class="gradient-primary p-6 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-blue-100 text-sm font-medium">RECORRIDO ACTIVO</p>
                                        <p class="text-2xl font-bold mt-1">En curso</p>
                                    </div>
                                    <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                                        <i class="fas fa-play-circle text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-route text-blue-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Ruta asignada</p>
                                            <p class="font-medium">{{ $recorridoActivo->ruta->nombre ?? 'Sin ruta' }}</p>
                                            @if($recorridoActivo->ruta)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-ruler mr-1"></i>
                                                Tolerancia: {{ $recorridoActivo->ruta->tolerancia_metros }} metros
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-truck text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Camión</p>
                                            <p class="font-medium">{{ $recorridoActivo->camion->placa ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">{{ $recorridoActivo->camion->codigo ?? '' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-clock text-purple-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Inicio del recorrido</p>
                                            <p class="font-medium">{{ $recorridoActivo->fecha_inicio->format('H:i:s') }}</p>
                                            <p class="text-xs text-gray-500">{{ $recorridoActivo->fecha_inicio->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t pt-4">
                                        <form method="POST" action="{{ route('conductor.recorrido.finalizar') }}">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('¿Estás seguro de finalizar el recorrido?')"
                                                    class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 
                                                           text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 
                                                           flex items-center justify-center">
                                                <i class="fas fa-stop-circle mr-2"></i>
                                                Finalizar Recorrido
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de GPS -->
                        <div class="bg-white rounded-xl card-shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-satellite mr-2 text-blue-600"></i>
                                Transmisión GPS
                            </h3>
                            
                            <div class="space-y-4">
                                <div id="gps-controls">
                                    <button id="btn_gps" 
                                            class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 
                                                   text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 
                                                   flex items-center justify-center hover-lift">
                                        <i class="fas fa-satellite-dish mr-2"></i>
                                        <span>Iniciar transmisión GPS</span>
                                    </button>
                                </div>
                                
                                <div id="gps-status" class="p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <div id="gps-indicator" class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div>
                                        <span id="gps-status-text" class="text-sm font-medium text-gray-700">GPS inactivo</span>
                                    </div>
                                    <div id="gps-details" class="text-xs text-gray-500">
                                        Presiona el botón para comenzar a enviar tu ubicación
                                    </div>
                                </div>
                                
                                <div class="text-xs text-gray-500 bg-blue-50 p-3 rounded-lg">
                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                    La transmisión GPS se enviará automáticamente cada 10 segundos mientras esté activa.
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas -->
                        <div class="bg-white rounded-xl card-shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                                Estadísticas
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-blue-600">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Puntos enviados</p>
                                        <p class="text-xl font-semibold" id="puntos-contador">0</p>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-green-600">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Última ubicación</p>
                                        <p class="text-xs font-medium" id="ultima-ubicacion">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- ========== FORMULARIO NUEVO CON SELECCIÓN DINÁMICA ========== -->
                        <div class="bg-white rounded-xl card-shadow overflow-hidden">
                            <div class="gradient-warning p-6 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-orange-100 text-sm font-medium">SIN RECORRIDO ACTIVO</p>
                                        <p class="text-2xl font-bold mt-1">Inicia uno nuevo</p>
                                    </div>
                                    <div class="bg-orange-400 bg-opacity-30 p-3 rounded-full">
                                        <i class="fas fa-plus-circle text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <form method="POST" action="{{ route('conductor.recorrido.iniciar') }}" id="form-inicio">
                                    @csrf
                                    
                                    <div class="space-y-4">
                                        <!-- Seleccionar Camión -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                <i class="fas fa-truck mr-1 text-gray-500"></i>
                                                Seleccionar Camión
                                            </label>
                                            <select name="camion_id" id="camion-select" required
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                <option value="">-- Selecciona un camión --</option>
                                                @foreach($camiones as $camion)
                                                    <option value="{{ $camion->id }}" 
                                                            data-rutas="{{ $camion->rutas->pluck('id') }}"
                                                            {{ old('camion_id') == $camion->id ? 'selected' : '' }}>
                                                        {{ $camion->placa }} - {{ $camion->codigo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- Seleccionar Ruta (se llena dinámicamente) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                <i class="fas fa-route mr-1 text-gray-500"></i>
                                                Seleccionar Ruta
                                            </label>
                                            <select name="ruta_id" id="ruta-select" required
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                <option value="">-- Primero selecciona un camión --</option>
                                                @foreach($rutas as $ruta)
                                                    <option value="{{ $ruta->id }}" 
                                                            data-inicio="{{ $ruta->horario_hoy['inicio'] ?? '08:00' }}"
                                                            data-fin="{{ $ruta->horario_hoy['fin'] ?? '17:00' }}"
                                                            data-camion="{{ $ruta->camion_asignado ?? '' }}">
                                                        {{ $ruta->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <!-- Información del horario de hoy -->
                                        <div id="info-horario" class="hidden p-4 bg-blue-50 rounded-lg border border-blue-200">
                                            <div class="flex items-start">
                                                <i class="fas fa-clock text-blue-500 mt-0.5 mr-2"></i>
                                                <div>
                                                    <p class="text-sm text-blue-800 font-medium">Horario de hoy:</p>
                                                    <p id="horario-texto" class="text-sm text-blue-600"></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="pt-2">
                                            <button type="submit" 
                                                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 
                                                           text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 
                                                           flex items-center justify-center hover-lift">
                                                <i class="fas fa-play-circle mr-2"></i>
                                                Iniciar Nuevo Recorrido
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Instrucciones -->
                        <div class="bg-white rounded-xl card-shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Instrucciones
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-1 text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Selecciona camión y ruta</p>
                                        <p class="text-sm text-gray-600">Elige el vehículo y la ruta asignada</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-2 text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Inicia el recorrido</p>
                                        <p class="text-sm text-gray-600">Comienza el seguimiento GPS automático</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-3 text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Activa el GPS</p>
                                        <p class="text-sm text-gray-600">Tu ubicación se enviará cada 10 segundos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Columna derecha - Mapa -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl card-shadow p-6 h-full">
                        <!-- Header con botones -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-map mr-2 text-blue-600"></i>
                                Mapa de Ubicación
                            </h3>
                            
                            <!-- Controles del mapa -->
                            <div class="flex items-center gap-2">
                                @if($recorridoActivo)
                                <span id="map-status" class="text-xs bg-gray-100 px-3 py-1.5 rounded-full text-gray-600 hidden sm:inline">
                                    Esperando GPS
                                </span>
                                @endif
                                
                                <!-- Botón Pantalla Completa -->
                                <button id="btn_fullscreen" class="text-sm bg-gray-100 hover:bg-gray-200 p-2 rounded-lg transition-colors" title="Pantalla completa">
                                    <i class="fas fa-expand"></i>
                                </button>
                                
                                <!-- Botón Centrar -->
                                <button id="btn_centrar" class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition-colors flex items-center gap-1">
                                    <i class="fas fa-crosshairs"></i>
                                    <span class="hidden sm:inline">Centrar</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Mapa -->
                        <div id="map" class="w-full h-[500px]"></div>
                        
                        @if(!$recorridoActivo)
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-map-marked-alt text-yellow-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-yellow-800 font-medium">Inicia un recorrido para ver el mapa</p>
                                    <p class="text-yellow-700 text-sm mt-1">Una vez que inicies el recorrido, podrás ver tu ubicación en tiempo real aquí.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== ACTUALIZAR HORA ==========
        function updateTime() {
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                          now.getMinutes().toString().padStart(2, '0');
            document.getElementById('current-time').textContent = timeStr;
        }
        updateTime();
        setInterval(updateTime, 60000);

        // ========== SELECCIÓN DINÁMICA DE CAMIÓN/RUTA (SOLO SIN RECORRIDO ACTIVO) ==========
        @if(!$recorridoActivo)
        const camionSelect = document.getElementById('camion-select');
        const rutaSelect = document.getElementById('ruta-select');
        const infoHorario = document.getElementById('info-horario');
        const horarioTexto = document.getElementById('horario-texto');
        
        // Datos de rutas desde PHP
        const rutasData = @json($rutas);
        
        // Función para filtrar rutas por camión
        function filtrarRutasPorCamion(camionId) {
            // Limpiar selector de rutas
            rutaSelect.innerHTML = '<option value="">-- Selecciona una ruta --</option>';
            rutaSelect.disabled = true;
            infoHorario.classList.add('hidden');
            
            if (!camionId) return;
            
            // Buscar el camión seleccionado
            const camionOption = Array.from(camionSelect.options).find(opt => opt.value === camionId);
            
            if (!camionOption) return;
            
            // Filtrar rutas que pertenecen a este camión
            const rutasCamion = rutasData.filter(ruta => {
                return ruta.camion_asignado === camionOption.text.split(' - ')[0];
            });
            
            if (rutasCamion.length > 0) {
                rutasCamion.forEach(ruta => {
                    const option = document.createElement('option');
                    option.value = ruta.id;
                    option.textContent = `${ruta.nombre} (${ruta.horario_hoy?.inicio || '08:00'} - ${ruta.horario_hoy?.fin || '17:00'})`;
                    option.dataset.inicio = ruta.horario_hoy?.inicio || '08:00';
                    option.dataset.fin = ruta.horario_hoy?.fin || '17:00';
                    rutaSelect.appendChild(option);
                });
                rutaSelect.disabled = false;
            }
        }
        
        // Evento change del camión
        if (camionSelect) {
            camionSelect.addEventListener('change', function() {
                filtrarRutasPorCamion(this.value);
            });
            
            // Si hay un valor preseleccionado, filtrar al cargar
            if (camionSelect.value) {
                filtrarRutasPorCamion(camionSelect.value);
            }
        }
        
        // Evento change de la ruta
        if (rutaSelect) {
            rutaSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                if (selectedOption && selectedOption.value) {
                    const inicio = selectedOption.dataset.inicio;
                    const fin = selectedOption.dataset.fin;
                    horarioTexto.textContent = `${inicio} - ${fin}`;
                    infoHorario.classList.remove('hidden');
                } else {
                    infoHorario.classList.add('hidden');
                }
            });
        }
        @endif

        // ========== FUNCIONALIDAD DEL MAPA (SOLO CON RECORRIDO ACTIVO) ==========
        @if($recorridoActivo)
        // Variables del mapa
        let mapConductor = null;
        let markerConductor = null;
        let rutaPlanificadaLayer = null;
        let recorridoRealLayer = null;
        let puntosRecorrido = [];
        let timer = null;
        let puntosEnviados = 0;
        let vistaInicializada = false;
        
        // Elementos DOM
        const gpsIndicator = document.getElementById('gps-indicator');
        const gpsStatusText = document.getElementById('gps-status-text');
        const gpsDetails = document.getElementById('gps-details');
        const btnGps = document.getElementById('btn_gps');
        const mapStatus = document.getElementById('map-status');
        const puntosContador = document.getElementById('puntos-contador');
        const ultimaUbicacion = document.getElementById('ultima-ubicacion');

        // Inicializar mapa
        function initMapConductor(lat = -17.7833, lng = -63.1821) {
            mapConductor = L.map('map').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapConductor);
            
            @if($recorridoActivo && $recorridoActivo->ruta)
                cargarRutaPlanificada();
            @endif
        }

        // Cargar ruta planificada
        function cargarRutaPlanificada() {
            @if($recorridoActivo && $recorridoActivo->ruta)
                try {
                    const rutaGeojson = @json($recorridoActivo->ruta->geometria_geojson ?? null);
                    if (rutaGeojson) {
                        const geoData = JSON.parse(rutaGeojson);
                        
                        rutaPlanificadaLayer = L.geoJSON(geoData, {
                            style: {
                                color: '#3b82f6',
                                weight: 4,
                                opacity: 0.6,
                                dashArray: '8, 8'
                            }
                        }).addTo(mapConductor);
                        
                        // Centrar solo la primera vez
                        const bounds = rutaPlanificadaLayer.getBounds();
                        if (bounds.isValid() && !vistaInicializada) {
                            mapConductor.fitBounds(bounds, { padding: [50, 50] });
                            vistaInicializada = true;
                        }
                        
                        // Marcadores de inicio y fin
                        const coords = geoData.coordinates || [];
                        if (coords.length > 0) {
                            const inicio = [coords[0][1], coords[0][0]];
                            L.marker(inicio, {
                                icon: L.divIcon({
                                    html: '<div style="background: #10b981; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border: 2px solid white;"><i class="fas fa-play text-xs"></i></div>',
                                    iconSize: [28, 28]
                                })
                            }).addTo(mapConductor).bindPopup('<b>Inicio de ruta</b>');
                            
                            const fin = [coords[coords.length - 1][1], coords[coords.length - 1][0]];
                            L.marker(fin, {
                                icon: L.divIcon({
                                    html: '<div style="background: #ef4444; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border: 2px solid white;"><i class="fas fa-flag-checkered text-xs"></i></div>',
                                    iconSize: [28, 28]
                                })
                            }).addTo(mapConductor).bindPopup('<b>Fin de ruta</b>');
                        }
                        
                        recorridoRealLayer = L.layerGroup().addTo(mapConductor);
                    }
                } catch (e) {
                    console.error('Error cargando ruta:', e);
                }
            @endif
        }

        // Mostrar ubicación
        function mostrarUbicacion(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            
            if (!mapConductor) initMapConductor(lat, lng);
            
            puntosRecorrido.push([lat, lng]);
            
            // Actualizar polyline
            if (recorridoRealLayer) mapConductor.removeLayer(recorridoRealLayer);
            recorridoRealLayer = L.polyline(puntosRecorrido, {
                color: '#ef4444',
                weight: 3,
                opacity: 0.9
            }).addTo(mapConductor);
            
            // Actualizar marcador
            if (!markerConductor) {
                markerConductor = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: '<div style="background: #8b5cf6; color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-truck text-sm"></i></div>',
                        iconSize: [36, 36],
                        className: 'custom-marker-conductor'
                    })
                }).addTo(mapConductor).bindPopup('<b>Tu ubicación actual</b>');
            } else {
                markerConductor.setLatLng([lat, lng]);
            }
            
            // Centrar en el primer punto
            if (puntosRecorrido.length === 1 && !vistaInicializada) {
                mapConductor.setView([lat, lng], 16);
                vistaInicializada = true;
            }
            
            // Actualizar UI
            ultimaUbicacion.textContent = lat.toFixed(4) + ', ' + lng.toFixed(4);
            if (mapStatus) mapStatus.textContent = 'GPS activo - ' + new Date().toLocaleTimeString();
        }

        // Enviar punto GPS
        async function enviarPuntoGPS(pos) {
            const payload = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                precision_m: pos.coords.accuracy,
                velocidad_mps: pos.coords.speed || 0,
                rumbo_grados: pos.coords.heading || 0,
                fecha_gps: new Date().toISOString().slice(0, 19).replace('T', ' ')
            };

            try {
                const response = await fetch("{{ route('conductor.gps.guardar') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    puntosEnviados++;
                    if (puntosContador) puntosContador.textContent = puntosEnviados;
                    
                    const now = new Date();
                    if (gpsDetails) {
                        gpsDetails.innerHTML = `
                            <strong>Último envío:</strong> ${now.toLocaleTimeString()}<br>
                            <strong>Precisión:</strong> ${pos.coords.accuracy.toFixed(1)} m<br>
                            ${pos.coords.speed ? `<strong>Velocidad:</strong> ${(pos.coords.speed * 3.6).toFixed(1)} km/h` : ''}
                        `;
                    }
                    return true;
                }
            } catch (error) {
                console.error('Error enviando GPS:', error);
                return false;
            }
        }

        // Ciclo GPS
        function cicloGPS() {
            if (!navigator.geolocation) {
                if (gpsStatusText) gpsStatusText.textContent = 'GPS no soportado';
                if (gpsIndicator) gpsIndicator.className = 'w-3 h-3 bg-red-500 rounded-full mr-2';
                return;
            }

            if (gpsStatusText) gpsStatusText.textContent = 'Obteniendo ubicación...';
            if (gpsIndicator) gpsIndicator.className = 'w-3 h-3 bg-yellow-500 rounded-full mr-2 pulse-animation';

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    mostrarUbicacion(pos);
                    const success = await enviarPuntoGPS(pos);
                    
                    if (success) {
                        if (gpsStatusText) gpsStatusText.textContent = 'GPS activo';
                        if (gpsIndicator) gpsIndicator.className = 'w-3 h-3 bg-green-500 rounded-full mr-2 pulse-animation';
                    }
                },
                (err) => {
                    console.error('Error GPS:', err);
                    if (gpsStatusText) gpsStatusText.textContent = 'Error GPS';
                    if (gpsIndicator) gpsIndicator.className = 'w-3 h-3 bg-red-500 rounded-full mr-2 pulse-animation';
                    if (mapStatus) mapStatus.textContent = 'Error GPS';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Botón GPS
        if (btnGps) {
            btnGps.addEventListener('click', function() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                    btnGps.innerHTML = '<i class="fas fa-satellite-dish mr-2"></i><span>Iniciar transmisión GPS</span>';
                    btnGps.className = btnGps.className.replace('from-red-500 to-red-600', 'from-green-500 to-green-600')
                                                       .replace('hover:from-red-600 hover:to-red-700', 'hover:from-green-600 hover:to-green-700');
                    if (gpsStatusText) gpsStatusText.textContent = 'GPS detenido';
                    if (gpsIndicator) gpsIndicator.className = 'w-3 h-3 bg-gray-400 rounded-full mr-2';
                    if (mapStatus) mapStatus.textContent = 'GPS detenido';
                    if (gpsDetails) gpsDetails.innerHTML = 'Transmisión detenida';
                } else {
                    cicloGPS();
                    timer = setInterval(cicloGPS, 10000);
                    btnGps.innerHTML = '<i class="fas fa-stop-circle mr-2"></i><span>Detener transmisión GPS</span>';
                    btnGps.className = btnGps.className.replace('from-green-500 to-green-600', 'from-red-500 to-red-600')
                                                       .replace('hover:from-green-600 hover:to-green-700', 'hover:from-red-600 hover:to-red-700');
                }
            });
        }

        // Botón pantalla completa
        function toggleFullscreen() {
            const mapContainer = document.getElementById('map');
            const btn = document.getElementById('btn_fullscreen');
            
            if (!document.fullscreenElement) {
                if (mapContainer.requestFullscreen) {
                    mapContainer.requestFullscreen();
                } else if (mapContainer.webkitRequestFullscreen) {
                    mapContainer.webkitRequestFullscreen();
                } else if (mapContainer.msRequestFullscreen) {
                    mapContainer.msRequestFullscreen();
                }
                if (btn) btn.innerHTML = '<i class="fas fa-compress"></i>';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                if (btn) btn.innerHTML = '<i class="fas fa-expand"></i>';
            }
        }

        // Botón centrar
        function centrarMapa() {
            if (puntosRecorrido.length > 0 && mapConductor) {
                const ultimo = puntosRecorrido[puntosRecorrido.length - 1];
                mapConductor.setView(ultimo, 16);
            } else if (rutaPlanificadaLayer && mapConductor) {
                const bounds = rutaPlanificadaLayer.getBounds();
                mapConductor.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        // Event listeners
        const btnFullscreen = document.getElementById('btn_fullscreen');
        if (btnFullscreen) btnFullscreen.addEventListener('click', toggleFullscreen);

        const btnCentrar = document.getElementById('btn_centrar');
        if (btnCentrar) btnCentrar.addEventListener('click', centrarMapa);

        // Detectar cambio de pantalla completa
        document.addEventListener('fullscreenchange', function() {
            const btn = document.getElementById('btn_fullscreen');
            if (btn) {
                btn.innerHTML = document.fullscreenElement ? 
                    '<i class="fas fa-compress"></i>' : 
                    '<i class="fas fa-expand"></i>';
            }
        });

        // Inicializar mapa
        initMapConductor();
        @endif
    });
    </script>
    <script>
    // ========== SISTEMA DE GEOLOCALIZACIÓN EN SEGUNDO PLANO ==========
    let worker = null;
    let workerActivo = false;

    function iniciarWorkerGPS() {
        if (window.Worker) {
            try {
                // Crear worker
                worker = new Worker('/js/geolocation-worker.js');
                
                // Configurar worker
                worker.postMessage({
                    tipo: 'INICIAR',
                    url: '{{ route("conductor.gps.multiples") }}',
                    token: document.querySelector('meta[name="csrf-token"]').content,
                    recorridoId: {{ $recorridoActivo->id ?? 'null' }}
                });
                
                // Escuchar mensajes del worker
                worker.onmessage = function(e) {
                    const data = e.data;
                    
                    switch(data.tipo) {
                        case 'INICIADO':
                            console.log('Worker GPS iniciado');
                            workerActivo = true;
                            actualizarEstadoWorker(true);
                            break;
                            
                        case 'DETENIDO':
                            console.log('Worker GPS detenido');
                            workerActivo = false;
                            actualizarEstadoWorker(false);
                            break;
                            
                        case 'POSICION':
                            // Actualizar UI con la última posición
                            actualizarUIconPosicion(data.punto);
                            break;
                            
                        case 'ENVIADO':
                            console.log(`Enviados ${data.cantidad} puntos`);
                            actualizarContadorPuntos(data.resultado.total);
                            break;
                            
                        case 'ERROR':
                            console.error('Error GPS:', data.mensaje);
                            mostrarErrorGPS(data.mensaje);
                            break;
                            
                        case 'ERROR_ENVIO':
                            console.warn(`Error enviando ${data.cantidad} puntos`);
                            break;
                    }
                };
                
                // Manejar errores del worker
                worker.onerror = function(error) {
                    console.error('Error en worker:', error);
                    workerActivo = false;
                    actualizarEstadoWorker(false);
                };
                
            } catch (e) {
                console.error('Error creando worker:', e);
                usarModoFallback();
            }
        } else {
            console.warn('Web Workers no soportados, usando modo fallback');
            usarModoFallback();
        }
    }

    function detenerWorkerGPS() {
        if (worker) {
            worker.postMessage({ tipo: 'DETENER' });
            setTimeout(() => {
                worker.terminate();
                worker = null;
                workerActivo = false;
                actualizarEstadoWorker(false);
            }, 500);
        }
    }

    function actualizarEstadoWorker(activo) {
        const btnGps = document.getElementById('btn_gps');
        const gpsIndicator = document.getElementById('gps-indicator');
        const gpsStatusText = document.getElementById('gps-status-text');
        
        if (activo) {
            btnGps.innerHTML = '<i class="fas fa-stop-circle mr-2"></i><span>Detener transmisión GPS</span>';
            btnGps.className = btnGps.className.replace('from-green-500 to-green-600', 'from-red-500 to-red-600');
            gpsIndicator.className = 'w-3 h-3 bg-green-500 rounded-full mr-2 pulse-animation';
            gpsStatusText.textContent = 'GPS activo (segundo plano)';
        } else {
            btnGps.innerHTML = '<i class="fas fa-satellite-dish mr-2"></i><span>Iniciar transmisión GPS</span>';
            btnGps.className = btnGps.className.replace('from-red-500 to-red-600', 'from-green-500 to-green-600');
            gpsIndicator.className = 'w-3 h-3 bg-gray-400 rounded-full mr-2';
            gpsStatusText.textContent = 'GPS inactivo';
        }
    }

    function actualizarUIconPosicion(punto) {
        // Actualizar mapa si existe
        if (typeof mostrarUbicacionConductor === 'function') {
            mostrarUbicacionConductor({
                coords: {
                    latitude: punto.lat,
                    longitude: punto.lng,
                    accuracy: punto.precision_m,
                    speed: punto.velocidad_mps
                }
            });
        }
        
        // Actualizar última ubicación
        document.getElementById('ultima-ubicacion').textContent = 
            punto.lat.toFixed(4) + ', ' + punto.lng.toFixed(4);
    }

    function actualizarContadorPuntos(total) {
        document.getElementById('puntos-contador').textContent = total;
    }

    function mostrarErrorGPS(mensaje) {
        const gpsStatusText = document.getElementById('gps-status-text');
        gpsStatusText.textContent = 'Error: ' + mensaje;
    }

    function usarModoFallback() {
        // Usar el sistema antiguo de setInterval
        console.log('Usando modo fallback');
        // Aquí va tu código anterior de cicloGPS
    }

    // Modificar el evento del botón GPS
    document.addEventListener('DOMContentLoaded', function() {
        const btnGps = document.getElementById('btn_gps');
        
        if (btnGps) {
            btnGps.addEventListener('click', function() {
                if (workerActivo) {
                    detenerWorkerGPS();
                } else {
                    iniciarWorkerGPS();
                }
            });
        }
    });
    </script>
</body>
</html>