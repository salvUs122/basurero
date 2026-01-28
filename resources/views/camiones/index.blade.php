<h1>Camiones</h1>

<a href="{{ route('camiones.create') }}">Crear camión</a>

<ul>
@foreach ($camiones as $camion)
    <li>
        {{ $camion->placa }} - {{ $camion->codigo }} - {{ $camion->estado }}
    </li>
@endforeach
</ul>
