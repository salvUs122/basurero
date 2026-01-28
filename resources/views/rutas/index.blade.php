<h1>Rutas</h1>

<a href="{{ route('rutas.create') }}">Crear ruta</a>

<ul>
@foreach ($rutas as $ruta)
    <li>
        {{ $ruta->nombre }} - {{ $ruta->estado }} - Tolerancia: {{ $ruta->tolerancia_metros }}m
    </li>
@endforeach
</ul>
