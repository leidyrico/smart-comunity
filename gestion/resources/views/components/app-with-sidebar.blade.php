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
    <body class="font-sans antialiased bg-gray-100">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
                <!-- Logo and title -->
                <div class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200">
                    <div class="flex items-center">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-8 w-auto">
                        <span class="ml-2 font-semibold text-lg text-gray-900">Residencias Alfa</span>
                    </div>
                    <!-- Close button for mobile -->
                    <button id="closeSidebar" class="lg:hidden text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <nav class="mt-6 px-3">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 mb-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                        Dashboard
                    </a>

                    <!-- Apartamentos -->
                    <div class="mb-2">
                        <button onclick="toggleSidebarSubmenu('apartments-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('apartamentos.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <i class="fas fa-building w-5 h-5 mr-3"></i>
                                Apartamentos
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="apartments-submenu" class="ml-6 mt-2 space-y-1 {{ request()->routeIs('apartamentos.*') ? '' : 'hidden' }}">
                            <a href="{{ route('apartamentos.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Ver Apartamentos
                            </a>
                            <a href="{{ route('apartamentos.create') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Nuevo Apartamento
                            </a>
                        </div>
                    </div>

                    <!-- Recibos -->
                    <div class="mb-2">
                        <button onclick="toggleSidebarSubmenu('receipts-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('recibos.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <i class="fas fa-receipt w-5 h-5 mr-3"></i>
                                Recibos
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="receipts-submenu" class="ml-6 mt-2 space-y-1 {{ request()->routeIs('recibos.*') ? '' : 'hidden' }}">
                            <a href="{{ route('recibos.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Ver Recibos
                            </a>
                            <a href="{{ route('recibos.create') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Crear Recibo
                            </a>
                            <a href="{{ route('recibos.asignar-manual') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Asignar Recibos Manual
                            </a>
                        </div>
                    </div>

                    <!-- Reserva de Espacios -->
                    <div class="mb-2">
                        <button onclick="toggleSidebarSubmenu('reservations-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('spaces.*') || request()->routeIs('reservations.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt w-5 h-5 mr-3"></i>
                                Reserva de Espacios
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="reservations-submenu" class="ml-6 mt-2 space-y-1 {{ request()->routeIs('spaces.*') || request()->routeIs('reservations.*') ? '' : 'hidden' }}">
                            <a href="{{ route('spaces.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Gestionar Espacios
                            </a>
                            <a href="{{ route('spaces.create') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Crear Espacio
                            </a>
                            <a href="{{ route('reservations.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Ver Reservas
                            </a>
                            <a href="{{ route('reservations.create') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Nueva Reserva
                            </a>
                        </div>
                    </div>

                    <!-- Gestión de Deudas -->
                    <a href="{{ route('deudas.index') }}" class="flex items-center px-3 py-2 mb-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('deudas.*') ? 'bg-blue-100 text-blue-700 border-r-4 border-blue-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-exclamation-triangle w-5 h-5 mr-3"></i>
                        Gestión de Deudas
                    </a>

                    <!-- Gestión de Egresos -->
                    <div class="mb-2">
                        <button onclick="toggleSidebarSubmenu('egresos-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('egresos.*') || request()->routeIs('proveedores.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <i class="fas fa-money-bill-wave w-5 h-5 mr-3"></i>
                                Gestión de Egresos
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="egresos-submenu" class="ml-6 mt-2 space-y-1 {{ request()->routeIs('egresos.*') || request()->routeIs('proveedores.*') ? '' : 'hidden' }}">
                            <a href="{{ route('proveedores.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Proveedores
                            </a>
                            <a href="{{ route('egresos.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Ver Egresos
                            </a>
                            <a href="{{ route('egresos.create') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Crear Egreso
                            </a>
                        </div>
                    </div>

                    <!-- Conciliación -->
                    <div class="mb-2">
                        <button onclick="toggleSidebarSubmenu('conciliacion-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('conciliacion.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <i class="fas fa-balance-scale w-5 h-5 mr-3"></i>
                                Conciliación
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="conciliacion-submenu" class="ml-6 mt-2 space-y-1 {{ request()->routeIs('conciliacion.*') ? '' : 'hidden' }}">
                            <a href="{{ route('conciliacion.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Conciliación
                            </a>
                            <a href="{{ route('conciliacion.recaudacion') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Recaudación
                            </a>
                        </div>
                    </div>

                    <!-- Gestión de Documentos -->
                    <div class="mb-2">
                        <button onclick="toggleSidebarSubmenu('docs-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('actas.*') || request()->routeIs('inventario.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <div class="flex items-center">
                                <i class="fas fa-file-alt w-5 h-5 mr-3"></i>
                                Gestión de Documentos
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="docs-submenu" class="ml-6 mt-2 space-y-1 {{ request()->routeIs('actas.*') || request()->routeIs('inventario.*') ? '' : 'hidden' }}">
                            <a href="{{ route('actas.create') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Crear Documento
                            </a>
                            <a href="{{ route('actas.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Ver Documentos
                            </a>
                            <a href="{{ route('actas.import') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Importar Documentos
                            </a>
                            <a href="{{ route('inventario.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                Inventario
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- User Menu at bottom -->
                <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200">
                    <div class="relative">
                        <button onclick="toggleSidebarSubmenu('user-submenu')" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-user w-5 h-5 mr-3"></i>
                                <div class="text-left">
                                    <div class="font-medium">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="user-submenu" class="hidden mt-2 space-y-1">
                            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors duration-200">
                                <i class="fas fa-cog w-4 h-4 mr-2"></i>
                                Perfil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt w-4 h-4 mr-2"></i>
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content area -->
            <div class="flex-1 flex flex-col lg:ml-0 main-content">
                <!-- Top bar for mobile -->
                <div class="lg:hidden bg-white shadow-sm border-b border-gray-200">
                    <div class="flex items-center justify-between h-16 px-4">
                        <button id="openSidebar" class="text-gray-600 hover:text-gray-900">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div class="flex items-center">
                            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-8 w-auto">
                            <span class="ml-2 text-gray-900 font-semibold text-lg">Residencias Alfa</span>
                        </div>
                        <div class="w-6"></div> <!-- Spacer for centering -->
                    </div>
                </div>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                    <div class="container mx-auto px-6 py-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <!-- Overlay for mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

        <!-- JavaScript -->
        <script>
            function toggleSidebarSubmenu(submenuId) {
                const submenu = document.getElementById(submenuId);
                const button = submenu.previousElementSibling;
                const arrow = button.querySelector('svg');
                
                submenu.classList.toggle('hidden');
                
                if (arrow) {
                    arrow.classList.toggle('rotate-180');
                }
            }

            // Mobile sidebar functionality
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const openBtn = document.getElementById('openSidebar');
            const closeBtn = document.getElementById('closeSidebar');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);

            // Close sidebar on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            });

            // Auto-expand active menu sections
            document.addEventListener('DOMContentLoaded', function() {
                const activeMenus = document.querySelectorAll('.bg-blue-100');
                activeMenus.forEach(menu => {
                    const submenu = menu.nextElementSibling;
                    if (submenu && submenu.classList.contains('hidden')) {
                        submenu.classList.remove('hidden');
                        const arrow = menu.querySelector('svg');
                        if (arrow) arrow.classList.add('rotate-180');
                    }
                });
            });
        </script>
        
        @stack('scripts')
        
        <style>
            @media print {
                /* Hide sidebar and mobile bar during printing */
                #sidebar, .lg\\:hidden {
                    display: none !important;
                }
                
                /* Adjust main content for printing */
                .flex-1.flex.flex-col {
                    margin-left: 0 !important;
                }
                
                main.flex-1 {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
                
                .container.mx-auto {
                    padding-top: 0 !important;
                }
            }

            /* Custom scrollbar for sidebar */
            #sidebar {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e0 #f7fafc;
            }

            #sidebar::-webkit-scrollbar {
                width: 6px;
            }

            #sidebar::-webkit-scrollbar-track {
                background: #f7fafc;
            }

            #sidebar::-webkit-scrollbar-thumb {
                background: #cbd5e0;
                border-radius: 3px;
            }

            #sidebar::-webkit-scrollbar-thumb:hover {
                background: #a0aec0;
            }

            /* Smooth transitions */
            .transition-transform {
                transition-property: transform;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                transition-duration: 300ms;
            }

            .rotate-180 {
                transform: rotate(180deg);
            }

            /* Ensure sidebar maintains consistent width */
            #sidebar {
                min-width: 16rem !important;
                max-width: 16rem !important;
                width: 16rem !important;
                flex-shrink: 0 !important;
            }

            /* Ensure main content doesn't affect sidebar */
            .main-content {
                min-width: 0;
                flex: 1;
                overflow-x: auto;
            }
        </style>
    </body>
</html>