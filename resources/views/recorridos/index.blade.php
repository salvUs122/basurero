<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Recorridos</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th align="left">ID</th>
                            <th align="left">Estado</th>
                            <th align="left">Ruta</th>
                            <th align="left">Camión</th>
                            <th align="left">Conductor</th>
                            <th align="left">Inicio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($recorridos as $r)
                        <tr style="border-top:1px solid #eee;">
                            <td>#{{ $r->id }}</td>
                            <td>{{ $r->estado }}</td>
                            <td>{{ $r->ruta?->nombre }}</td>
                            <td>{{ $r->camion?->placa }}</td>
                            <td>{{ $r->conductor?->name }}</td>
                            <td>{{ $r->fecha_inicio }}</td>
                            <td><a href="{{ route('recorridos.show', $r) }}">Ver</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div style="margin-top:12px;">
                    {{ $recorridos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
