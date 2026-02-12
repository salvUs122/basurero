<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Recorrido #{{ $recorrido->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div id="map" style="height: 520px; border:1px solid #ddd;"></div>
                <p id="estado" style="margin-top:10px;color:#555;">Cargando…</p>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const map = L.map('map').setView([-17.7833, -63.1821], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Ruta planificada (azul)
        // Ruta planificada (azul)
        const geo = @json($recorrido->ruta?->geometria_geojson);
        let rutaLatLngs = [];
        let plan = null;

        if (geo) {
            const geom = JSON.parse(geo); // {type:"LineString", coordinates:[[lng,lat],...]}
            const feature = { type: "Feature", geometry: geom, properties: {} };

            plan = L.geoJSON(feature, {
                style: { color: 'blue', weight: 5, opacity: 0.9 }
            }).addTo(map);

            map.fitBounds(plan.getBounds(), { padding: [20,20] });

            // guardamos puntos de la ruta para calcular distancia
            rutaLatLngs = plan.getLayers()[0].getLatLngs();
        }

        // Recorrido real (rojo) + marcador
        let poly = L.polyline([], { color: 'red', weight: 4, opacity: 0.9 }).addTo(map);

        let marker = null;
        const tolerancia = {{ (int)($recorrido->ruta?->tolerancia_metros ?? 50) }};
        let capaFuera = L.layerGroup().addTo(map);

        function distToSegmentMeters(p, a, b) {
            const R = 6371000;
            function toXY(ll, ref) {
                const x = (ll.lng - ref.lng) * Math.cos((ref.lat*Math.PI/180)) * (Math.PI/180) * R;
                const y = (ll.lat - ref.lat) * (Math.PI/180) * R;
                return {x,y};
            }
            const ref = a;
            const P = toXY(p, ref), A = toXY(a, ref), B = toXY(b, ref);
            const ABx = B.x - A.x, ABy = B.y - A.y;
            const APx = P.x - A.x, APy = P.y - A.y;
            const ab2 = ABx*ABx + ABy*ABy;
            const t = ab2 === 0 ? 0 : (APx*ABx + APy*ABy) / ab2;
            const tt = Math.max(0, Math.min(1, t));
            const Cx = A.x + tt*ABx, Cy = A.y + tt*ABy;
            const dx = P.x - Cx, dy = P.y - Cy;
            return Math.sqrt(dx*dx + dy*dy);
        }

        function distToPolylineMeters(p, poly) {
            if (!poly || poly.length < 2) return Infinity;
            let min = Infinity;
            for (let i = 0; i < poly.length - 1; i++) {
                const d = distToSegmentMeters(p, poly[i], poly[i+1]);
                if (d < min) min = d;
            }
            return min;
        }


        async function cargarPuntos() {
            const res = await fetch("{{ route('recorridos.puntos', $recorrido) }}", { credentials: "same-origin" });
            const data = await res.json();

            const latlngs = data.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
            poly.setLatLngs(latlngs);
            if (latlngs.length > 1) {
                const bounds = L.latLngBounds(latlngs);
                map.fitBounds(bounds, { padding: [30, 30] });
            }

            // marcar fuera de ruta
            capaFuera.clearLayers();
            let fuera = 0;

            if (rutaLatLngs.length >= 2) {
                data.forEach(pt => {
                    const p = L.latLng(parseFloat(pt.lat), parseFloat(pt.lng));
                    const d = distToPolylineMeters(p, rutaLatLngs);

                    if (d > tolerancia) {
                        fuera++;
                        L.circleMarker(p, {
                        radius: 7,
                        color: 'red',
                        fillColor: 'red',
                        fillOpacity: 0.9,
                        weight: 2
                        }).addTo(capaFuera)
                        .bindPopup(`Fuera de ruta ~ ${Math.round(d)} m`);

                    }
                });
            }


            if (latlngs.length > 0) {
                const last = latlngs[latlngs.length - 1];
                if (!marker) marker = L.marker(last).addTo(map);
                else marker.setLatLng(last);
                document.getElementById('estado').textContent =
                `Puntos: ${latlngs.length} | Fuera de ruta: ${fuera} | Tolerancia: ${tolerancia} m | Último: ${(data[data.length-1].fecha_gps ?? '')}`;

            } else {
                document.getElementById('estado').textContent = "Aún sin puntos GPS.";
            }
        }

        cargarPuntos();
        setInterval(cargarPuntos, 5000); // polling cada 5s
    });
    </script>
</x-app-layout>
