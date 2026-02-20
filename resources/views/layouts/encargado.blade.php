<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Encargado</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- ========== NAVBAR PARA ENCARGADO ========== -->
        <nav class="bg-gradient-to-r from-green-600 to-green-800 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo y menú izquierdo -->
                    <div class="flex items-center space-x-8">
                        <!-- Logo -->
                        <a href="{{ route('encargado.dashboard') }}" class="flex items-center space-x-2">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                            <span class="font-bold text-xl">Panel Encargado</span>
                        </a>
                        
                        
                    </div>

                    <!-- Menú derecho (usuario) -->
                    <div class="flex items-center space-x-4">
                        <span class="text-sm hidden md:block">{{ Auth::user()->name }}</span>
                        
                        <!-- Dropdown de usuario -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 hover:bg-green-700 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-user-circle text-xl"></i>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50" style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-user mr-2"></i> Perfil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Salir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        @if(isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Alpine.js para dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    @stack('scripts')
</body>
</html>