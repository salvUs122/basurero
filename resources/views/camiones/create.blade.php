<h1>Crear Camión</h1>

<form method="POST" action="{{ route('camiones.store') }}">
    @csrf

    <div>
        <label>Placa</label>
        <input type="text" name="placa">
    </div>

    <div>
        <label>Código</label>
        <input type="text" name="codigo">
    </div>

    <div>
        <label>Estado</label>
        <select name="estado">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
            <option value="mantenimiento">Mantenimiento</option>
        </select>
    </div>

    <button type="submit">Guardar</button>
</form>
