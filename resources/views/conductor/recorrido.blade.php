<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-white p-4 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-full mr-3">
                    <i class="fas fa-truck text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-base sm:text-lg md:text-xl text-gray-800">
                        🚚 Mi Recorrido
                    </h2>
                    <p class="text-xs text-gray-500">Panel del conductor</p>
                </div>
            </div>
            <div class="flex items-center bg-gray-100 px-3 py-1.5 rounded-full w-full sm:w-auto">
                <i class="far fa-clock mr-1 text-gray-600 text-xs sm:text-sm"></i>
                <span id="current-time" class="text-xs sm:text-sm font-medium text-gray-700">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-3 sm:py-4 md:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
            
            <!-- Mensajes - Optimizados para móvil -->
            @if(session('success'))
                <div class="mb-3 sm:mb-4 md:mb-6 bg-green-50 border-l-4 border-green-500 p-3 sm:p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-base sm:text-lg"></i>
                        </div>
                        <div class="ml-2 sm:ml-3">
                            <p class="text-xs sm:text-sm text-green-800 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-3 sm:mb-4 md:mb-6 bg-red-50 border-l-4 border-red-500 p-3 sm:p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-base sm:text-lg"></i>
                        </div>
                        <div class="ml-2 sm:ml-3">
                            <p class="text-xs sm:text-sm text-red-800 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-4 sm:gap-5 md:gap-6">
                <!-- Columna izquierda - Información (ORDEN: Primero en móvil) -->
                <div class="lg:w-1/3 space-y-4 sm:space-y-5 order-2 lg:order-1">
                    @if($recorridoActivo)
                        <!-- Tarjeta de recorrido activo - OPTIMIZADA -->
                        <div class="bg-white rounded-xl shadow-sm sm:shadow-lg overflow-hidden border border-gray-200">
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 sm:p-5 md:p-6 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-blue-100 text-xs sm:text-sm font-medium uppercase tracking-wider">RECORRIDO ACTIVO</p>
                                        <p class="text-xl sm:text-2xl font-bold mt-1">En curso</p>
                                    </div>
                                    <div class="bg-blue-500 bg-opacity-30 p-2 sm:p-2.5 md:p-3 rounded-full">
                                        <i class="fas fa-play-circle text-xl sm:text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 sm:p-5 md:p-6">
                                <div class="space-y-3 sm:space-y-4">
                                    <!-- Información de ruta -->
                                    <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-route text-blue-600 text-sm sm:text-base"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-500">Ruta asignada</p>
                                            <p class="text-sm sm:text-base font-medium text-gray-800 truncate">{{ $recorridoActivo->ruta->nombre ?? 'Sin ruta' }}</p>
                                            @if($recorridoActivo->ruta)
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                <i class="fas fa-ruler mr-1"></i>
                                                Tolerancia: {{ $recorridoActivo->ruta->tolerancia_metros }}m
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Información de camión -->
                                    <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                                        <div class="bg-green-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-truck text-green-600 text-sm sm:text-base"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Camión</p>
                                            <p class="text-sm sm:text-base font-medium text-gray-800">{{ $recorridoActivo->camion->placa ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">{{ $recorridoActivo->camion->codigo ?? '' }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Información de tiempo -->
                                    <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-clock text-purple-600 text-sm sm:text-base"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Inicio del recorrido</p>
                                            <p class="text-sm sm:text-base font-medium text-gray-800">{{ $recorridoActivo->fecha_inicio->format('H:i:s') }}</p>
                                            <p class="text-xs text-gray-500">{{ $recorridoActivo->fecha_inicio->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Botón para finalizar -->
                                    <div class="border-t pt-3 sm:pt-4">
                                        <form method="POST" action="{{ route('conductor.recorrido.finalizar') }}">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('¿Finalizar recorrido?')"
                                                    class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 
                                                           text-white font-medium py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-300 
                                                           flex items-center justify-center text-sm sm:text-base">
                                                <i class="fas fa-stop-circle mr-2"></i>
                                                Finalizar Recorrido
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de GPS - OPTIMIZADO -->
                        <div class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-5 md:p-6 border border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4 flex items-center">
                                <i class="fas fa-satellite mr-2 text-blue-600 text-sm sm:text-base"></i>
                                Transmisión GPS
                                <span class="ml-auto text-xs bg-gray-100 px-2 py-1 rounded-full">Beta</span>
                            </h3>
                            
                            <div class="space-y-3 sm:space-y-4">
                                <div id="gps-controls">
                                    <button id="btn_gps" 
                                            class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 
                                                   text-white font-medium py-3 sm:py-3.5 px-4 rounded-lg transition-all duration-300 
                                                   flex items-center justify-center text-sm sm:text-base shadow-md">
                                        <i class="fas fa-satellite-dish mr-2"></i>
                                        <span id="btn_gps_text">Iniciar transmisión</span>
                                    </button>
                                </div>
                                
                                <div id="gps-status" class="p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center mb-2">
                                        <div id="gps-indicator" class="w-2.5 h-2.5 bg-gray-400 rounded-full mr-2 animate-pulse"></div>
                                        <span id="gps-status-text" class="text-xs sm:text-sm font-medium text-gray-700">GPS inactivo</span>
                                    </div>
                                    <div id="gps-details" class="text-xs text-gray-500 break-words">
                                        Presiona el botón para comenzar
                                    </div>
                                </div>
                                
                                <div class="text-xs text-gray-500 bg-blue-50 p-2.5 sm:p-3 rounded-lg border border-blue-100">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mr-1.5 mt-0.5"></i>
                                        <span>Envío cada 10 segundos. Mantén la pantalla activa.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas - OPTIMIZADAS -->
                        <div class="bg-white rounded-xl shadow-sm sm:shadow-lg p-4 sm:p-5 md:p-6 border border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">
                                <i class="fas fa-chart-line mr-2 text-blue-600 text-sm sm:text-base"></i>
                                Estadísticas
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-2 sm:gap-3 md:gap-4">
                                <div class="bg-blue-50 p-3 sm:p-4 rounded-lg text-center border border-blue-100">
                                    <div class="text-xl sm:text-2xl font-bold text-blue-600">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </div>
                                    <div class="mt-1 sm:mt-2">
                                        <p class="text-xs text-gray-600">Puntos</p>
                                        <p class="text-lg sm:text-xl font-semibold text-gray-800" id="puntos-contador">0</p>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 p-3 sm:p-4 rounded-lg text-center border border-green-100">
                                    <div class="text-xl sm:text-2xl font-bold text-green-600">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="mt-1 sm:mt-2">
                                        <p class="text-xs text-gray-600">Última</p>
                                        <p class="text-xs font-medium text-gray-800 truncate" id="ultima-ubicacion">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- Formulario para iniciar recorrido - OPTIMIZADO -->
                        <div class="bg-white rounded-xl shadow-sm sm:shadow-lg overflow-hidden border border-gray-200">
                            <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-4 sm:p-5 md:p-6 text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-orange-100 text-xs sm:text-sm font-medium uppercase tracking-wider">SIN RECORRIDO</p>
                                        <p class="text-xl sm:text-2xl font-bold mt-1">Inicia uno nuevo</p>
                                    </div>
                                    <div class="bg-orange-400 bg-opacity-30 p-2 sm:p-2.5 md:p-3 rounded-full">
                                        <i class="fas fa-plus-circle text-xl sm:text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 sm:p-5 md:p-6">
                                <form method="POST" action="{{ route('conductor.recorrido.iniciar') }}" id="form-iniciar-recorrido">
                                    @csrf
                                    
                                    <div class="space-y-3 sm:space-y-4">
                                        <div>
                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                                                <i class="fas fa-truck mr-1 text-gray-500"></i>
                                                Camión
                                            </label>
                                            <select name="camion_id" required
                                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg 
                                                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                                           text-sm sm:text-base bg-white">
                                                <option value="">Seleccionar</option>
                                                @foreach($camiones as $camion)
                                                    <option value="{{ $camion->id }}">
                                                        {{ $camion->placa }} - {{ $camion->codigo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                                                <i class="fas fa-route mr-1 text-gray-500"></i>
                                                Ruta
                                            </label>
                                            <select name="ruta_id" required
                                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg 
                                                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                                           text-sm sm:text-base bg-white">
                                                <option value="">Seleccionar</option>
                                                @foreach($rutas as $ruta)
                                                    <option value="{{ $ruta->id }}">
                                                        {{ $ruta->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="pt-2 sm:pt-3">
                                            <button type="submit" 
                                                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 
                                                           text-white font-medium py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-300 
                                                           flex items-center justify-center text-sm sm:text-base shadow-md">
                                                <i class="fas fa-play-circle mr-2"></i>
                                                Iniciar Recorrido
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Columna derecha - Mapa (ORDEN: Primero en móvil) -->
                <div class="lg:w-2/3 order-1 lg:order-2">
                    <div class="bg-white rounded-xl shadow-sm sm:shadow-lg p-3 sm:p-4 md:p-6 h-full border border-gray-200 sticky top-4">
                        <div class="flex justify-between items-center mb-2 sm:mb-3 md:mb-4">
                            <h3 class="text-sm sm:text-base md:text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-map mr-2 text-blue-600 text-sm sm:text-base"></i>
                                Mapa de Ubicación
                            </h3>
                            @if($recorridoActivo)
                            <div class="text-xs bg-gray-100 px-2 sm:px-3 py-1 rounded-full flex items-center">
                                <span id="map-status" class="text-gray-600">Esperando GPS</span>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Contenedor del mapa con altura responsiva -->
                        <div id="map" class="w-full h-[350px] sm:h-[400px] md:h-[500px] lg:h-[550px] rounded-lg border border-gray-300"></div>
                        
                        @if(!$recorridoActivo)
                        <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-map-marked-alt text-yellow-500 text-base sm:text-lg"></i>
                                </div>
                                <div class="ml-2 sm:ml-3">
                                    <p class="text-xs sm:text-sm text-yellow-800 font-medium">Inicia un recorrido</p>
                                    <p class="text-xs text-yellow-700 mt-0.5">El mapa se activará automáticamente.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script optimizado con soporte para móvil
        document.addEventListener('DOMContentLoaded', function() {
            // Actualizar hora actual cada minuto
            function updateTime() {
                const now = new Date();
                const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                              now.getMinutes().toString().padStart(2, '0');
                const timeElement = document.getElementById('current-time');
                if (timeElement) timeElement.textContent = timeStr;
            }
            updateTime();
            setInterval(updateTime, 60000);

            // Detectar si es dispositivo móvil
            const isMobile = window.innerWidth < 768;
            
            @if($recorridoActivo)
            // Variables
            let mapConductor = null;
            let markerConductor = null;
            let rutaPlanificadaLayer = null;
            let recorridoRealLayer = null;
            let puntosRecorrido = [];
            let timer = null;
            let puntosEnviados = 0;
            let wakeLock = null;
            
            // Elementos DOM
            const gpsIndicator = document.getElementById('gps-indicator');
            const gpsStatusText = document.getElementById('gps-status-text');
            const gpsDetails = document.getElementById('gps-details');
            const btnGps = document.getElementById('btn_gps');
            const btnGpsText = document.getElementById('btn_gps_text');
            const mapStatus = document.getElementById('map-status');
            const puntosContador = document.getElementById('puntos-contador');
            const ultimaUbicacion = document.getElementById('ultima-ubicacion');

            // Solicitar Wake Lock para mantener pantalla encendida (Android Chrome)
            async function requestWakeLock() {
                if ('wakeLock' in navigator && isMobile) {
                    try {
                        wakeLock = await navigator.wakeLock.request('screen');
                        wakeLock.addEventListener('release', () => {
                            console.log('Wake Lock released');
                        });
                        console.log('Wake Lock activado');
                    } catch (err) {
                        console.error('Error con Wake Lock:', err);
                    }
                }
            }

            // Liberar Wake Lock
            async function releaseWakeLock() {
                if (wakeLock) {
                    try {
                        await wakeLock.release();
                        wakeLock = null;
                    } catch (err) {
                        console.error('Error liberando Wake Lock:', err);
                    }
                }
            }

            // Inicializar mapa con altura responsiva
            function initMapConductor(lat = -17.7833, lng = -63.1821) {
                const mapElement = document.getElementById('map');
                if (!mapElement) return;
                
                // Ajustar altura según dispositivo
                if (isMobile) {
                    mapElement.style.height = '350px';
                }
                
                mapConductor = L.map('map', {
                    zoomControl: !isMobile, // Ocultar controles en móvil
                    attributionControl: !isMobile
                }).setView([lat, lng], isMobile ? 14 : 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(mapConductor);
                
                // Agregar controles táctiles para móvil
                if (isMobile) {
                    L.control.zoom({ position: 'bottomright' }).addTo(mapConductor);
                }
                
                @if($recorridoActivo && $recorridoActivo->ruta)
                    cargarRutaPlanificadaConductor();
                @endif
            }

            // Cargar ruta planificada
            function cargarRutaPlanificadaConductor() {
                @if($recorridoActivo && $recorridoActivo->ruta)
                    try {
                        const rutaGeojson = @json($recorridoActivo->ruta->geometria_geojson ?? null);
                        if (rutaGeojson && mapConductor) {
                            const geoData = JSON.parse(rutaGeojson);
                            
                            rutaPlanificadaLayer = L.geoJSON(geoData, {
                                style: {
                                    color: '#3b82f6',
                                    weight: isMobile ? 3 : 4,
                                    opacity: 0.7,
                                    dashArray: '5, 10'
                                }
                            }).addTo(mapConductor);
                            
                            // Ajustar vista si hay puntos
                            const bounds = rutaPlanificadaLayer.getBounds();
                            if (bounds.isValid()) {
                                mapConductor.fitBounds(bounds, { 
                                    padding: isMobile ? [30, 30] : [50, 50] 
                                });
                            }
                            
                            recorridoRealLayer = L.layerGroup().addTo(mapConductor);
                        }
                    } catch (e) {
                        console.error('Error cargando ruta:', e);
                    }
                @endif
            }

            // Mostrar ubicación con optimizaciones para móvil
            function mostrarUbicacionConductor(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                
                if (!mapConductor) initMapConductor(lat, lng);
                
                puntosRecorrido.push([lat, lng]);
                
                if (recorridoRealLayer && mapConductor) {
                    if (mapConductor.hasLayer(recorridoRealLayer)) {
                        mapConductor.removeLayer(recorridoRealLayer);
                    }
                    
                    recorridoRealLayer = L.polyline(puntosRecorrido, {
                        color: '#ef4444',
                        weight: isMobile ? 4 : 3,
                        opacity: 0.9
                    }).addTo(mapConductor);
                }
                
                if (!markerConductor && mapConductor) {
                    markerConductor = L.marker([lat, lng], {
                        icon: L.divIcon({
                            html: `<div style="background: #8b5cf6; color: white; border-radius: 50%; 
                                          width: ${isMobile ? 28 : 32}px; height: ${isMobile ? 28 : 32}px; 
                                          display: flex; align-items: center; justify-content: center; 
                                          border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                                      <i class="fas fa-truck" style="font-size: ${isMobile ? '12px' : '14px'}"></i>
                                   </div>`,
                            iconSize: [isMobile ? 28 : 32, isMobile ? 28 : 32],
                            className: 'custom-marker-conductor'
                        })
                    }).addTo(mapConductor)
                    .bindPopup('<b>Tu ubicación</b>');
                } else if (markerConductor) {
                    markerConductor.setLatLng([lat, lng]);
                }
                
                // Centrar mapa en móvil solo al inicio
                if (puntosRecorrido.length === 1) {
                    mapConductor.setView([lat, lng], isMobile ? 15 : 16);
                }
                
                if (ultimaUbicacion) {
                    ultimaUbicacion.textContent = lat.toFixed(4) + ', ' + lng.toFixed(4);
                }
                if (mapStatus) {
                    mapStatus.textContent = 'GPS activo';
                }
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
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    if (response.ok) {
                        puntosEnviados++;
                        if (puntosContador) puntosContador.textContent = puntosEnviados;
                        
                        const now = new Date();
                        if (gpsDetails) {
                            gpsDetails.innerHTML = `
                                <div class="space-y-0.5">
                                    <div><span class="font-medium">Último:</span> ${now.toLocaleTimeString()}</div>
                                    <div><span class="font-medium">Precisión:</span> ${pos.coords.accuracy.toFixed(1)}m</div>
                                    ${pos.coords.speed ? `<div><span class="font-medium">Velocidad:</span> ${(pos.coords.speed * 3.6).toFixed(1)}km/h</div>` : ''}
                                </div>
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
                    if (gpsIndicator) gpsIndicator.className = 'w-2.5 h-2.5 bg-red-500 rounded-full mr-2 animate-pulse';
                    return;
                }

                if (gpsStatusText) gpsStatusText.textContent = 'Obteniendo ubicación...';
                if (gpsIndicator) gpsIndicator.className = 'w-2.5 h-2.5 bg-yellow-500 rounded-full mr-2 animate-pulse';

                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        mostrarUbicacionConductor(pos);
                        const success = await enviarPuntoGPS(pos);
                        
                        if (success) {
                            if (gpsStatusText) gpsStatusText.textContent = 'GPS activo';
                            if (gpsIndicator) gpsIndicator.className = 'w-2.5 h-2.5 bg-green-500 rounded-full mr-2 animate-pulse';
                        }
                    },
                    (err) => {
                        console.error('Error GPS:', err);
                        if (gpsStatusText) gpsStatusText.textContent = 'Error GPS';
                        if (gpsIndicator) gpsIndicator.className = 'w-2.5 h-2.5 bg-red-500 rounded-full mr-2 animate-pulse';
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            }

            // Control botón GPS
            if (btnGps) {
                btnGps.addEventListener('click', async function(e) {
                    e.preventDefault();
                    
                    if (timer) {
                        // Detener
                        clearInterval(timer);
                        timer = null;
                        await releaseWakeLock();
                        
                        btnGpsText.textContent = 'Iniciar transmisión';
                        btnGps.className = btnGps.className
                            .replace('from-red-500 to-red-600', 'from-green-500 to-green-600')
                            .replace('hover:from-red-600 hover:to-red-700', 'hover:from-green-600 hover:to-green-700');
                        
                        if (gpsStatusText) gpsStatusText.textContent = 'GPS detenido';
                        if (gpsIndicator) gpsIndicator.className = 'w-2.5 h-2.5 bg-gray-400 rounded-full mr-2';
                        if (gpsDetails) gpsDetails.innerHTML = 'Transmisión detenida';
                    } else {
                        // Iniciar
                        cicloGPS();
                        timer = setInterval(cicloGPS, 10000);
                        await requestWakeLock();
                        
                        btnGpsText.textContent = 'Detener transmisión';
                        btnGps.className = btnGps.className
                            .replace('from-green-500 to-green-600', 'from-red-500 to-red-600')
                            .replace('hover:from-green-600 hover:to-green-700', 'hover:from-red-600 hover:to-red-700');
                    }
                });
            }

            // Inicializar mapa
            initMapConductor();
            @endif

            // Prevenir zoom con dos dedos en móvil
            if (isMobile) {
                document.addEventListener('touchstart', (e) => {
                    if (e.touches.length > 1) e.preventDefault();
                }, { passive: false });
            }
        });
    </script>

    <style>
        /* Estilos responsivos */
        .custom-marker-conductor {
            background: none !important;
            border: none !important;
        }
        
        .leaflet-popup-content {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
            margin: 8px 12px;
        }
        
        @media (max-width: 640px) {
            .leaflet-control-zoom {
                margin-bottom: 70px !important;
            }
            
            #map {
                touch-action: pan-x pan-y;
            }
            
            select, button {
                min-height: 44px; /* Tamaño mínimo táctil */
            }
        }
        
        /* Animación para indicador GPS */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</x-app-layout>