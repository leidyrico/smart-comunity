<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Residencias Alfa') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Main content -->
            <div>
                <!-- Top navigation -->
                <nav class="bg-white shadow">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex items-center space-x-8">
                                <!-- Logo and title -->
                                <div class="flex items-center">
                                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-8 w-auto">
                                    <span class="ml-2 text-gray-900 font-semibold text-lg">Residencias Alfa</span>
                                </div>
                                
                                <!-- Navigation Menu -->
                                <nav class="hidden md:flex space-x-8">
                                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 text-sm font-medium transition-colors duration-200">
                                        Dashboard
                                    </a>
                                    
                                    <!-- Gestión de Apartamentos Dropdown -->
                                    <div class="relative">
                                        <button class="{{ request()->routeIs('apartamentos.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center" onclick="toggleDropdown('apartments-nav-dropdown')">
                                            Apartamentos
                                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div id="apartments-nav-dropdown" class="hidden absolute left-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                            <a href="{{ route('apartamentos.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver Apartamentos</a>
                                            <a href="{{ route('apartamentos.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Nuevo Apartamento</a>
                                            {{-- <a href="{{ route('apartamentos.import') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Importar Apartamentos</a> --}}
                                        </div>
                                    </div>
                                    
                                    <!-- Gestión de Recibos Dropdown -->
                                    <div class="relative">
                                        <button class="{{ request()->routeIs('recibos.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center" onclick="toggleDropdown('receipts-nav-dropdown')">
                                            Recibos
                                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div id="receipts-nav-dropdown" class="hidden absolute left-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                            <a href="{{ route('recibos.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver Recibos</a>
                                            <a href="{{ route('recibos.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Crear Recibo</a>
                                            <a href="{{ route('recibos.asignar-manual') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Asignar Recibos Manual</a>
                                        </div>
                                    </div>
                                    
                                    <!-- Gestión de Deudas Dropdown -->
                                    <div class="relative">
                                        <button class="{{ request()->routeIs('inquilinos.*') || request()->routeIs('deudas.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center" onclick="toggleDropdown('debts-nav-dropdown')">
                                            Gestión de Deudas
                                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div id="debts-nav-dropdown" class="hidden absolute left-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                            <a href="{{ route('deudas.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Detalle de deudas</a>
                                            <a href="{{ route('recibos.asignar-manual') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Asignar Recibos Manual</a>
                                            <div class="relative group">
                                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center justify-between" onclick="toggleSubDropdown('deuda-general-dropdown')">
                                                    Deuda General
                                                    <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                                <div id="deuda-general-dropdown" class="hidden absolute left-full top-0 ml-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                                    <a href="{{ route('inquilinos.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ingresar Deuda</a>
                                                    <a href="{{ route('inquilinos.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Resumen por Unidad</a>
                                                    <a href="{{ route('inquilinos.import') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Importar Deudas</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Gestión de Documentos Dropdown -->
                                    <div class="relative">
                                        <button class="{{ request()->routeIs('actas.*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }} px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center" onclick="toggleDropdown('docs-nav-dropdown')">
                                            Gestión de Documentos
                                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div id="docs-nav-dropdown" class="hidden absolute left-0 top-full mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-50">
                                            <a href="{{ route('actas.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Crear Documento</a>
                                            <a href="{{ route('actas.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver Documentos</a>
                                            <a href="{{ route('actas.import') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Importar Documentos</a>
                                        </div>
                                    </div>
                                </nav>
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
                            <div class="md:hidden flex items-center space-x-2">
                                <!-- Mobile menu button -->
                                <button type="button" class="bg-white p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" onclick="toggleMobileMenu()">
                                    <span class="sr-only">Open main menu</span>
                                    <svg id="mobile-menu-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                    <svg id="mobile-close-icon" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                
                                <!-- User menu button -->
                                <button type="button" class="bg-white p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" onclick="toggleDropdown('user-menu')">
                                    <span class="sr-only">Open user menu</span>
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Mobile menu -->
                <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            Dashboard
                        </a>
                        
                        <!-- Gestión de Apartamentos -->
                        <div>
                            <button class="{{ request()->routeIs('apartamentos.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} w-full text-left pl-3 pr-4 py-2 border-l-4 text-base font-medium flex items-center justify-between" onclick="toggleMobileSubmenu('apartments-mobile-submenu')">
                                Apartamentos
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="apartments-mobile-submenu" class="hidden pl-6 space-y-1">
                                <a href="{{ route('apartamentos.index') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Ver Apartamentos</a>
                                <a href="{{ route('apartamentos.create') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Nuevo Apartamento</a>
                                {{-- <a href="{{ route('apartamentos.import') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Importar Apartamentos</a> --}}
                            </div>
                        </div>
                        
                        <!-- Gestión de Recibos -->
                        <div>
                            <button class="{{ request()->routeIs('recibos.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} w-full text-left pl-3 pr-4 py-2 border-l-4 text-base font-medium flex items-center justify-between" onclick="toggleMobileSubmenu('receipts-mobile-submenu')">
                                Recibos
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="receipts-mobile-submenu" class="hidden pl-6 space-y-1">
                                <a href="{{ route('recibos.index') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Ver Recibos</a>
                                <a href="{{ route('recibos.create') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Crear Recibo</a>
                                <a href="{{ route('recibos.asignar-manual') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Asignar Recibos Manual</a>
                            </div>
                        </div>
                        
                        <!-- Gestión de Deudas -->
                        <div>
                            <button class="{{ request()->routeIs('inquilinos.*') || request()->routeIs('deudas.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} w-full text-left pl-3 pr-4 py-2 border-l-4 text-base font-medium flex items-center justify-between" onclick="toggleMobileSubmenu('debts-mobile-submenu')">
                                Gestión de Deudas
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="debts-mobile-submenu" class="hidden pl-6 space-y-1">
                                <a href="{{ route('deudas.index') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Detalle de deudas</a>
                                <div>
                                    <button class="w-full text-left py-2 text-sm text-gray-600 hover:text-gray-800 flex items-center justify-between" onclick="toggleMobileSubmenu('deuda-general-mobile-submenu')">
                                        Deuda General
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="deuda-general-mobile-submenu" class="hidden pl-4 space-y-1">
                                        <a href="{{ route('inquilinos.create') }}" class="block py-2 text-xs text-gray-500 hover:text-gray-700">Ingresar Deuda</a>
                                        <a href="{{ route('inquilinos.index') }}" class="block py-2 text-xs text-gray-500 hover:text-gray-700">Resumen por Unidad</a>
                                        <a href="{{ route('inquilinos.import') }}" class="block py-2 text-xs text-gray-500 hover:text-gray-700">Importar Deudas</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gestión de Documentos -->
                        <div>
                            <button class="{{ request()->routeIs('actas.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} w-full text-left pl-3 pr-4 py-2 border-l-4 text-base font-medium flex items-center justify-between" onclick="toggleMobileSubmenu('docs-mobile-submenu')">
                                Gestión de Documentos
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="docs-mobile-submenu" class="hidden pl-6 space-y-1">
                                <a href="{{ route('actas.create') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Crear Documento</a>
                                <a href="{{ route('actas.index') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Ver Documentos</a>
                                <a href="{{ route('actas.import') }}" class="block py-2 text-sm text-gray-600 hover:text-gray-800">Importar Documentos</a>
                            </div>
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
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- JavaScript for dropdowns and mobile menu -->
        <script>
            function toggleDropdown(dropdownId) {
                const dropdown = document.getElementById(dropdownId);
                const isHidden = dropdown.classList.contains('hidden');
                
                // Close all other dropdowns
                document.querySelectorAll('[id$="-dropdown"]').forEach(el => {
                    if (el.id !== dropdownId) {
                        el.classList.add('hidden');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('hidden');
            }

            function toggleSubDropdown(dropdownId) {
                const dropdown = document.getElementById(dropdownId);
                dropdown.classList.toggle('hidden');
            }

            function toggleMobileSubmenu(submenuId) {
                const submenu = document.getElementById(submenuId);
                submenu.classList.toggle('hidden');
            }

            function toggleMobileMenu() {
                const mobileMenu = document.getElementById('mobile-menu');
                const menuIcon = document.getElementById('mobile-menu-icon');
                const closeIcon = document.getElementById('mobile-close-icon');
                
                mobileMenu.classList.toggle('hidden');
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            }

            function toggleMobileSubmenu(submenuId) {
                const submenu = document.getElementById(submenuId);
                submenu.classList.toggle('hidden');
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('[id$="-dropdown"]');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.closest('.relative').contains(event.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            });
        </script>
        
        @stack('scripts')
        
        <style>
            @media print {
                /* Ocultar toda la navegación durante la impresión */
                nav.bg-white.shadow {
                    display: none !important;
                }
                
                /* Ocultar menú móvil */
                #mobile-menu {
                    display: none !important;
                }
                
                /* Ajustar el contenido principal para que ocupe toda la página */
                main.flex-1 {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
                
                .container.mx-auto {
                    padding-top: 0 !important;
                }
            }
        </style>
    </body>
</html>