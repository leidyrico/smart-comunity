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
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <!-- Logo -->
                <div class="mb-8">
                    <img src="/logo.png" alt="Residencias Alfa Logo" class="mx-auto h-32 w-auto">
                </div>
                
                <!-- Mensaje de Bienvenida -->
                <h1 class="text-5xl font-bold text-gray-900 mb-12">
                    Bienvenidos Residencias Alfa
                </h1>
                
                <!-- Opciones de Autenticación -->
                @if (Route::has('login'))
                    <div class="space-y-4">
                        @auth
                            <div>
                                <a href="{{ url('/dashboard') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white px-8 py-4 rounded-lg text-lg font-medium transition-colors shadow-lg">
                                    Ir al Dashboard
                                </a>
                            </div>
                        @else
                            <div class="space-x-4">
                                <a href="{{ route('login') }}" class="inline-block bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-lg text-lg font-medium transition-colors">
                                    Iniciar Sesión
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-8 py-4 rounded-lg text-lg font-medium transition-colors">
                                        Registrarse
                                    </a>
                                @endif
                            </div>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
