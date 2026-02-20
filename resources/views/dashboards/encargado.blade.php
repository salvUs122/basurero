<x-encargado-layout>
        <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    📋 Panel de Encargado
                </h2>
                <p class="text-sm text-gray-600 mt-1">Bienvenido, {{ Auth::user()->name }}</p>
            </div>
            <div class="text-sm text-gray-600">{{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Camiones</p>
                            <p class="text-3xl font-bold mt-2">{{ $totalCamiones }}</p>
                        </div>
                        <div class="bg-blue-400 p-3 rounded-full">
                            <i class="fas fa-truck text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Camiones Activos</p>
                            <p class="text-3xl font-bold mt-2">{{ $camionesActivos }}</p>
                        </div>
                        <div class="bg-green-400 p-3 rounded-full">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Rutas Activas</p>
                            <p class="text-3xl font-bold mt-2">{{ $totalRutas }}</p>
                        </div>
                        <div class="bg-purple-400 p-3 rounded-full">
                            <i class="fas fa-route text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Recorridos Hoy</p>
                            <p class="text-3xl font-bold mt-2">{{ $recorridosHoy }}</p>
                        </div>
                        <div class="bg-orange-400 p-3 rounded-full">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de acceso rápido -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('camiones.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:scale-105">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-truck text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Camiones</h3>
                            <p class="text-sm text-gray-500">Gestionar flota</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('rutas.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:scale-105">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-route text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Rutas</h3>
                            <p class="text-sm text-gray-500">Administrar rutas</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('monitoreo.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:scale-105">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-satellite-dish text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Monitoreo</h3>
                            <p class="text-sm text-gray-500">Seguimiento GPS</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('recorridos.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:scale-105">
                    <div class="flex items-center">
                        <div class="bg-orange-100 p-3 rounded-lg">
                            <i class="fas fa-history text-orange-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Recorridos</h3>
                            <p class="text-sm text-gray-500">Historial</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-encargado-layout>