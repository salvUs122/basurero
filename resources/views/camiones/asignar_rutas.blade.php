<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar rutas a {{ $camion->placa }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">

                <form method="POST" action="{{ route('camiones.guardar_rutas', $camion) }}">
                    @csrf

                    <p>Marca las rutas que este camión puede realizar:</p>

                    <div style="margin-top: 12px;">
                        @foreach ($rutas as $ruta)
                            <div>
                                <label>
                                    <input type="checkbox" name="rutas[]" value="{{ $ruta->id }}"
                                           {{ in_array($ruta->id, $asignadas) ? 'checked' : '' }}>
                                    {{ $ruta->nombre }} ({{ $ruta->estado }})
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" style="margin-top: 12px;">Guardar</button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
