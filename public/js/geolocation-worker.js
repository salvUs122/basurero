// Worker para geolocalización en segundo plano
let watchId = null;
let intervalo = null;
let url = '';
let token = '';
let recorridoId = null;
let ultimoEnvio = 0;
let puntosPendientes = [];

// Escuchar mensajes desde la aplicación principal
self.addEventListener('message', function(e) {
    const data = e.data;
    
    switch(data.tipo) {
        case 'INICIAR':
            iniciarSeguimiento(data);
            break;
        case 'DETENER':
            detenerSeguimiento();
            break;
        case 'ACTUALIZAR_CONFIG':
            actualizarConfig(data);
            break;
    }
});

function iniciarSeguimiento(data) {
    url = data.url;
    token = data.token;
    recorridoId = data.recorridoId;
    
    if (!navigator.geolocation) {
        self.postMessage({ tipo: 'ERROR', mensaje: 'Geolocalización no soportada' });
        return;
    }
    
    // Opciones de alta precisión
    const opciones = {
        enableHighAccuracy: true,
        maximumAge: 0,
        timeout: 10000
    };
    
    // Iniciar watchPosition para seguimiento continuo
    watchId = navigator.geolocation.watchPosition(
        posicionRecibida,
        errorRecibido,
        opciones
    );
    
    // Intervalo para enviar puntos pendientes (cada 10 segundos)
    intervalo = setInterval(enviarPuntosPendientes, 10000);
    
    self.postMessage({ tipo: 'INICIADO' });
}

function detenerSeguimiento() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
    
    if (intervalo !== null) {
        clearInterval(intervalo);
        intervalo = null;
    }
    
    // Enviar puntos pendientes antes de detener
    if (puntosPendientes.length > 0) {
        enviarPuntosPendientes();
    }
    
    self.postMessage({ tipo: 'DETENIDO' });
}

function actualizarConfig(data) {
    if (data.url) url = data.url;
    if (data.token) token = data.token;
    if (data.recorridoId) recorridoId = data.recorridoId;
}

function posicionRecibida(posicion) {
    const punto = {
        lat: posicion.coords.latitude,
        lng: posicion.coords.longitude,
        precision_m: posicion.coords.accuracy,
        velocidad_mps: posicion.coords.speed,
        rumbo_grados: posicion.coords.heading,
        fecha_gps: new Date().toISOString().slice(0, 19).replace('T', ' '),
        timestamp: Date.now()
    };
    
    // Agregar a puntos pendientes
    puntosPendientes.push(punto);
    
    // Enviar inmediatamente si hay pocos puntos
    if (puntosPendientes.length >= 3) {
        enviarPuntosPendientes();
    }
    
    // Notificar a la app principal
    self.postMessage({
        tipo: 'POSICION',
        punto: punto,
        pendientes: puntosPendientes.length
    });
}

function errorRecibido(error) {
    self.postMessage({
        tipo: 'ERROR',
        codigo: error.code,
        mensaje: error.message
    });
}

async function enviarPuntosPendientes() {
    if (puntosPendientes.length === 0) return;
    
    const puntosAEnviar = [...puntosPendientes];
    puntosPendientes = [];
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                puntos: puntosAEnviar,
                recorrido_id: recorridoId
            })
        });
        
        if (response.ok) {
            const resultado = await response.json();
            self.postMessage({
                tipo: 'ENVIADO',
                cantidad: puntosAEnviar.length,
                resultado: resultado
            });
        } else {
            // Si hay error, devolver puntos a la cola
            puntosPendientes = [...puntosAEnviar, ...puntosPendientes];
            self.postMessage({
                tipo: 'ERROR_ENVIO',
                cantidad: puntosAEnviar.length,
                status: response.status
            });
        }
    } catch (error) {
        // Si hay error de red, devolver puntos a la cola
        puntosPendientes = [...puntosAEnviar, ...puntosPendientes];
        self.postMessage({
            tipo: 'ERROR_ENVIO',
            cantidad: puntosAEnviar.length,
            error: error.message
        });
    }
}