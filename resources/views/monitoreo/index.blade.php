<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Monitoreo en tiempo real
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabla de estado de recorridos -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-truck mr-2 text-blue-600"></i>
                        Recorridos Activos
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="tabla-estado">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Camión</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conductor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruta</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Última Actualización</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puntos</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Se llenará con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mapa principal -->
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if($recorridosActivos->count() === 0)
                    <div class="text-center py-12">
                        <i class="fas fa-road text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700">No hay recorridos activos</h3>
                        <p class="text-sm text-gray-500 mt-1">Todos los camiones están fuera de servicio o en mantenimiento.</p>
                    </div>
                @else
                    <div id="map" style="height: 600px; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
                    
                    <!-- Selector de recorrido -->
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-medium text-gray-700">
                                <i class="fas fa-filter mr-1"></i>
                                Filtrar recorrido:
                            </label>
                            <select id="sel_recorrido" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="todos">Mostrar todos los recorridos</option>
                                @foreach($recorridosActivos as $r)
                                    <option value="{{ $r->id }}">
                                        #{{ $r->id }} | {{ $r->camion->placa ?? 'Sin camión' }} | {{ $r->ruta->nombre ?? 'Sin ruta' }}
                                    </option>
                                @endforeach
                            </select>
                            <span id="info" class="text-sm text-gray-600 ml-4"></span>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                            Alertas Recientes
                        </h3>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <ul id="lista_eventos" class="space-y-2">
                                <li class="text-sm text-gray-600">Cargando alertas...</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($recorridosActivos->count() > 0)
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let map;
        let markers = {};
        let polylines = {};
        let routeLayers = {};
        
        // Inicializar mapa
        function initMap() {
            map = L.map('map').setView([-17.7833, -63.1821], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
        }
        
        // Función para mostrar alertas de un recorrido específico
        async function cargarEventos(id) {
            const ul = document.getElementById('lista_eventos');
            if (!ul) return;

            try {
                const response = await fetch(`/monitoreo/${id}/eventos`);
                const eventos = await response.json();

                ul.innerHTML = '';

                if (!Array.isArray(eventos) || eventos.length === 0) {
                    const li = document.createElement('li');
                    li.className = 'text-green-600 text-sm';
                    li.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Sin alertas';
                    ul.appendChild(li);
                    return;
                }

                eventos.forEach(ev => {
                    const li = document.createElement('li');
                    li.className = 'text-red-700 text-sm flex items-start';
                    li.innerHTML = `
                        <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                        <div>
                            <strong>${ev.tipo?.replace('_', ' ') || 'Alerta'}:</strong> ${ev.mensaje || ''}<br>
                            <span class="text-gray-600 text-xs">${ev.fecha_evento || ''}</span>
                        </div>
                    `;
                    ul.appendChild(li);
                });
            } catch (error) {
                console.error('Error cargando eventos:', error);
            }
        }

        // Cargar puntos en tiempo real
        async function cargarPuntosActivos() {
            try {
                const response = await fetch("{{ route('monitoreo.puntos_activos') }}");
                const recorridos = await response.json();
                
                // Obtener recorrido seleccionado
                const recorridoSeleccionado = document.getElementById('sel_recorrido').value;
                
                // Limpiar marcadores antiguos
                Object.values(markers).forEach(marker => {
                    if (marker && map) map.removeLayer(marker);
                });
                markers = {};
                
                // Limpiar polylines antiguos
                Object.values(polylines).forEach(polyline => {
                    if (polyline && map) map.removeLayer(polyline);
                });
                polylines = {};
                
                // Limpiar rutas planificadas
                Object.values(routeLayers).forEach(layer => {
                    if (layer && map) map.removeLayer(layer);
                });
                routeLayers = {};
                
                // Array para bounds
                let bounds = null;
                
                // Procesar cada recorrido
                recorridos.forEach(recorrido => {
                    // Si hay un filtro y no es "todos", saltar los no seleccionados
                    if (recorridoSeleccionado !== 'todos' && 
                        String(recorrido.recorrido_id) !== String(recorridoSeleccionado)) {
                        return;
                    }
                    
                    // ========== DIBUJAR RUTA PLANIFICADA COMPLETA ==========
                    if (recorrido.ruta_coords && recorrido.ruta_coords.length > 0) {
                        // Convertir coordenadas [lng, lat] a [lat, lng] para Leaflet
                        const rutaLatLngs = recorrido.ruta_coords.map(coord => [coord[1], coord[0]]);
                        
                        // Dibujar ruta planificada (azul)
                        L.polyline(rutaLatLngs, {
                            color: '#3b82f6',
                            weight: 4,
                            opacity: 0.6,
                            dashArray: '5, 10',
                            className: 'ruta-planificada'
                        }).addTo(map).bindPopup(`
                            <b>Ruta: ${recorrido.ruta}</b><br>
                            Tolerancia: ${recorrido.tolerancia} metros
                        `);
                        
                        // Agregar marcadores en los puntos clave de la ruta
                        if (rutaLatLngs.length > 0) {
                            // Marcador de inicio
                            L.marker(rutaLatLngs[0], {
                                icon: L.divIcon({
                                    html: '<div style="background: #10b981; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-play text-xs"></i></div>',
                                    iconSize: [24, 24]
                                })
                            }).addTo(map).bindPopup('<b>Inicio de ruta</b>');
                            
                            // Marcador de fin
                            L.marker(rutaLatLngs[rutaLatLngs.length - 1], {
                                icon: L.divIcon({
                                    html: '<div style="background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-flag-checkered text-xs"></i></div>',
                                    iconSize: [24, 24]
                                })
                            }).addTo(map).bindPopup('<b>Fin de ruta</b>');
                            
                            // Actualizar bounds con la ruta planificada
                            const rutaBounds = L.latLngBounds(rutaLatLngs);
                            bounds = bounds ? bounds.extend(rutaBounds) : rutaBounds;
                        }
                    }
                    
                    // ========== DIBUJAR RECORRIDO REAL ==========
                    if (recorrido.puntos && recorrido.puntos.length > 0) {
                        // Crear polyline para el recorrido real
                        const puntosLatLng = recorrido.puntos.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
                        
                        polylines[recorrido.recorrido_id] = L.polyline(puntosLatLng, {
                            color: '#ef4444',
                            weight: 3,
                            opacity: 0.9
                        }).addTo(map);
                        
                        // Agregar marcador para el último punto
                        const ultimoPunto = recorrido.puntos[recorrido.puntos.length - 1];
                        if (ultimoPunto) {
                            markers[recorrido.recorrido_id] = L.marker(
                                [parseFloat(ultimoPunto.lat), parseFloat(ultimoPunto.lng)], 
                                {
                                    icon: L.divIcon({
                                        html: `<div style="background: #3b82f6; color: white; border-radius: 50%; 
                                               width: 30px; height: 30px; display: flex; align-items: center; 
                                               justify-content: center; font-weight: bold; border: 2px solid white;
                                               box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                               <i class="fas fa-truck text-sm"></i>
                                              </div>`,
                                        iconSize: [30, 30],
                                        className: 'custom-marker'
                                    })
                                }
                            ).addTo(map)
                            .bindPopup(`
                                <div style="min-width: 200px;">
                                    <b><i class="fas fa-truck mr-1"></i>${recorrido.camion}</b><br>
                                    <i class="fas fa-user mr-1"></i>${recorrido.conductor}<br>
                                    <i class="fas fa-route mr-1"></i>${recorrido.ruta}<br>
                                    <i class="fas fa-clock mr-1"></i>${new Date(ultimoPunto.fecha_gps).toLocaleTimeString()}<br>
                                    <i class="fas fa-map-marker mr-1"></i>${recorrido.total_puntos} puntos<br>
                                    ${ultimoPunto.velocidad_mps ? `<i class="fas fa-tachometer-alt mr-1"></i>${(ultimoPunto.velocidad_mps * 3.6).toFixed(1)} km/h` : ''}
                                </div>
                            `);
                        }
                        
                        // Actualizar bounds con el recorrido real
                        if (puntosLatLng.length > 0) {
                            const recorridoBounds = L.latLngBounds(puntosLatLng);
                            bounds = bounds ? bounds.extend(recorridoBounds) : recorridoBounds;
                        }
                    }
                });
                
                // Ajustar vista del mapa
                if (bounds) {
                    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                }
                
                // Actualizar tabla de estado
                actualizarTablaEstado(recorridos);
                
                // Cargar alertas del primer recorrido o del seleccionado
                if (recorridos.length > 0) {
                    const idParaAlertas = recorridoSeleccionado === 'todos' 
                        ? recorridos[0].recorrido_id 
                        : recorridoSeleccionado;
                    cargarEventos(idParaAlertas);
                }
                
            } catch (error) {
                console.error('Error cargando puntos:', error);
                document.getElementById('info').textContent = 'Error cargando datos';
            }
        }

        // Actualizar tabla de estado
        function actualizarTablaEstado(recorridos) {
            const tbody = document.querySelector('#tabla-estado tbody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            recorridos.forEach(recorrido => {
                const ultimoPunto = recorrido.ultimo_punto;
                const ahora = new Date();
                const ultimaFecha = ultimoPunto ? new Date(ultimoPunto.fecha_gps) : null;
                const segundosDiff = ultimaFecha ? Math.floor((ahora - ultimaFecha) / 1000) : null;
                
                let estado = 'OK';
                let color = 'green';
                let icon = 'check-circle';
                
                if (!ultimoPunto) {
                    estado = 'SIN GPS';
                    color = 'orange';
                    icon = 'exclamation-triangle';
                } else if (segundosDiff > 30) {
                    estado = 'GPS ATRASADO';
                    color = 'orange';
                    icon = 'clock';
                } else if (recorrido.eventos_fuera_ruta > 0) {
                    estado = 'FUERA DE RUTA';
                    color = 'red';
                    icon = 'exclamation-circle';
                }
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-truck text-blue-600 text-sm"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">${recorrido.camion}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${recorrido.conductor}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${recorrido.ruta}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            ${ultimaFecha ? ultimaFecha.toLocaleTimeString() : 'N/A'}
                        </div>
                        <div class="text-xs text-gray-500">
                            ${ultimaFecha ? ultimaFecha.toLocaleDateString() : ''}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                              style="color: ${color}; background-color: ${color}20;">
                            <i class="fas fa-${icon} mr-1"></i>
                            ${estado}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${recorrido.total_puntos}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Inicializar cuando el DOM esté listo
        initMap();
        cargarPuntosActivos();
        
        // Configurar el selector de recorrido
        document.getElementById('sel_recorrido').addEventListener('change', cargarPuntosActivos);
        
        // Actualizar cada 5 segundos
        setInterval(cargarPuntosActivos, 5000);
    });
    </script>
    @endif

    <style>
        .custom-marker {
            background: none !important;
            border: none !important;
        }
        .leaflet-popup-content {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .ruta-planificada {
            animation: pulse-line 2s infinite;
        }
        
        @keyframes pulse-line {
            0% { opacity: 0.6; }
            50% { opacity: 0.9; }
            100% { opacity: 0.6; }
        }
    </style>
</x-app-layout>