<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Ruta
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">

                <form method="POST" action="{{ route('rutas.store') }}">
                    @csrf

                    <div>
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>

                    <div>
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                        </select>
                    </div>

                    <div>
                        <label>Tolerancia (metros)</label>
                        <input type="number" name="tolerancia_metros" value="50" min="5" max="500" required>
                    </div>

                    <hr>

                    <p><b>Dibuja la ruta en el mapa (línea).</b></p>

                    <div id="map" style="height: 450px; border: 1px solid #ccc;"></div>

                    <input type="hidden" name="geometria_geojson" id="geometria_geojson" required>

                    <p id="estado_dibujo" style="color: #b00; margin-top: 10px;">
                        Aún no dibujaste la ruta.
                    </p>

                    <button type="submit">Guardar</button>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof L === 'undefined') {
                        alert('Leaflet no está cargado. Revisa el layout.');
                        return;
                    }

                    const map = L.map('map').setView([-17.7833, -63.1821], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    const drawnItems = new L.FeatureGroup();
                    map.addLayer(drawnItems);

                    const drawControl = new L.Control.Draw({
                        edit: { featureGroup: drawnItems },
                        draw: {
                            polygon: false,
                            rectangle: false,
                            circle: false,
                            circlemarker: false,
                            marker: false,
                            polyline: true
                        }
                    });
                    map.addControl(drawControl);

                    const inputGeojson = document.getElementById('geometria_geojson');
                    const estadoDibujo = document.getElementById('estado_dibujo');

                    function actualizarGeojson() {
                        const data = drawnItems.toGeoJSON();
                        const lineas = data.features.filter(f => f.geometry && f.geometry.type === 'LineString');

                        if (lineas.length === 1) {
                            inputGeojson.value = JSON.stringify(lineas[0].geometry);
                            estadoDibujo.style.color = 'green';
                            estadoDibujo.textContent = 'Ruta dibujada correctamente ✅';
                        } else {
                            inputGeojson.value = '';
                            estadoDibujo.style.color = '#b00';
                            estadoDibujo.textContent = 'Debes dibujar EXACTAMENTE 1 línea (ruta).';
                        }
                    }

                    map.on(L.Draw.Event.CREATED, function (event) {
                        drawnItems.clearLayers();
                        drawnItems.addLayer(event.layer);
                        actualizarGeojson();
                    });

                    map.on(L.Draw.Event.EDITED, function () {
                        actualizarGeojson();
                    });

                    map.on(L.Draw.Event.DELETED, function () {
                        actualizarGeojson();
                    });

                    document.querySelector('form').addEventListener('submit', function (e) {
                        if (!inputGeojson.value) {
                            e.preventDefault();
                            alert('Debes dibujar una ruta (una línea) en el mapa antes de guardar.');
                        }
                    });
                });
                </script>

            </div>
        </div>
    </div>
</x-app-layout>
