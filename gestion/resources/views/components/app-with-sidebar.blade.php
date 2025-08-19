<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Navigation Header -->
            <nav class="bg-white shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}">
                                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-8 w-auto">
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                    Dashboard
                                </a>
                                
                                <!-- Gestión de Actas -->
                                <div class="relative inline-flex items-center px-1 pt-1">
                                    <button class="{{ request()->routeIs('actas.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" onclick="toggleDropdown('actas-dropdown')">
                                        Gestión de Actas
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="actas-dropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                        <a href="{{ route('actas.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Crear Acta</a>
                                        <a href="{{ route('actas.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver Actas</a>
                                        <a href="{{ route('actas.import') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Importar Actas</a>
                                    </div>
                                </div>

                                <!-- Gestión de Inquilinos -->
                                <div class="relative inline-flex items-center px-1 pt-1">
                                    <button class="{{ request()->routeIs('inquilinos.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" onclick="toggleDropdown('inquilinos-dropdown')">
                                        Gestión de Deudas
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="inquilinos-dropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                        <a href="{{ route('inquilinos.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ingresar Deuda</a>
                                        <a href="{{ route('inquilinos.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Lista de Apartamentos</a>
                                        <a href="{{ route('inquilinos.import') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Importar Deudas</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="relative">
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleDropdown('user-dropdown')">
                                    <div>{{ Auth::user()->name }}</div>
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="user-dropdown" class="hidden absolute right-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none" onclick="toggleMobileMenu()">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div id="mobile-menu" class="hidden sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            Dashboard
                        </a>
                        
                        <!-- Mobile Actas Menu -->
                        <div class="border-l-4 border-transparent">
                            <div class="pl-3 pr-4 py-2 text-base font-medium text-gray-600">Gestión de Actas</div>
                            <a href="{{ route('actas.create') }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Crear Acta</a>
                            <a href="{{ route('actas.index') }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Ver Actas</a>
                            <a href="{{ route('actas.import') }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Importar Actas</a>
                        </div>
                        
                        <!-- Mobile Inquilinos Menu -->
                        <div class="border-l-4 border-transparent">
                            <div class="pl-3 pr-4 py-2 text-base font-medium text-gray-600">Gestión de Deudas</div>
                            <a href="{{ route('inquilinos.create') }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Ingresar Deuda</a>
                            <a href="{{ route('inquilinos.index') }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Lista de Apartamentos</a>
                            <a href="{{ route('inquilinos.import') }}" class="block pl-6 pr-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Importar Deudas</a>
                        </div>
                    </div>
                    
                    <!-- Mobile User Menu -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- JavaScript for dropdowns and mobile menu -->
        <script>
            function toggleDropdown(dropdownId) {
                const dropdown = document.getElementById(dropdownId);
                const allDropdowns = document.querySelectorAll('[id$="-dropdown"]');
                
                // Close all other dropdowns
                allDropdowns.forEach(dd => {
                    if (dd.id !== dropdownId) {
                        dd.classList.add('hidden');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('hidden');
            }
            
            function toggleMobileMenu() {
                const mobileMenu = document.getElementById('mobile-menu');
                mobileMenu.classList.toggle('hidden');
            }
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('[id$="-dropdown"]');
                const buttons = document.querySelectorAll('button[onclick*="toggleDropdown"]');
                
                let clickedButton = false;
                buttons.forEach(button => {
                    if (button.contains(event.target)) {
                        clickedButton = true;
                    }
                });
                
                if (!clickedButton) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.add('hidden');
                    });
                }
            });
        </script>
    </body>
</html>