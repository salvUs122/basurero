<h1>Crear Ruta</h1>

<form method="POST" action="{{ route('rutas.store') }}">
    @csrf

    <div>
        <label>Nombre</label>
        <input type="text" name="nombre">
    </div>

    <div>
        <label>Estado</label>
        <select name="estado">
            <option value="activa">Activa</option>
            <option value="inactiva">Inactiva</option>
        </select>
    </div>

    <div>
        <label>Tolerancia (metros)</label>
        <input type="number" name="tolerancia_metros" value="50">
    </div>

    <button type="submit">Guardar</button>
</form>
