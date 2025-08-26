<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Residencias Alfa</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="{{ asset('js/app.js') }}" defer></script>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            'sans': ['Inter', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            'primary': {
                                50: '#f0f9ff',
                                500: '#0ea5e9',
                                600: '#0284c7',
                                700: '#0369a1',
                            }
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
        <div class="min-h-screen flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 flex items-center">
                                <svg class="h-8 w-8 text-primary-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                                <h1 class="text-2xl font-bold text-gray-900">Residencias Alfa</h1>
                            </div>
                        </div>
                        
                        @if (Route::has('login'))
                            <nav class="flex space-x-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                        Iniciar Sesión
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                            Registrarse
                                        </a>
                                    @endif
                                @endauth
                            </nav>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl w-full">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-gray-900 mb-4">
                            Bienvenido a <span class="text-primary-600">Residencias Alfa</span>
                        </h2>
                        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                            Sistema de gestión integral para comunidades residenciales. Administra pagos, recibos, deudas y más de manera eficiente.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8 mb-12">
                        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Gestión de Pagos</h3>
                            <p class="text-gray-600">Control completo de pagos y cuotas de mantenimiento</p>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Recibos Digitales</h3>
                            <p class="text-gray-600">Generación y envío automático de recibos</p>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow">
                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Administración</h3>
                            <p class="text-gray-600">Gestión de residentes y propiedades</p>
                        </div>
                    </div>

                    @guest
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                                Acceder al Sistema
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </div>
                    @endguest
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center text-sm text-gray-500">
                        <p>&copy; {{ date('Y') }} Residencias Alfa. Sistema de Gestión v{{ Illuminate\Foundation\Application::VERSION }}</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
