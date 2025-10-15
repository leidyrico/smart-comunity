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

        <!-- Styles / Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @if(file_exists(public_path('js/app.js')) && filesize(public_path('js/app.js')) > 0)
            <script src="{{ asset('js/app.js') }}" defer></script>
        @endif
        <!-- Flatpickr for consistent dd/MM/yyyy calendar display -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js" defer></script>
        
        <!-- Configuración adicional de Tailwind para mejor compatibilidad -->
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#3B82F6',
                            secondary: '#6B7280',
                            success: '#10B981',
                            warning: '#F59E0B',
                            danger: '#EF4444',
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
        
        <!-- Global date input calendar with Flatpickr showing dd/MM/yyyy, submitting ISO -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (!window.flatpickr) return;
                const dateInputs = document.querySelectorAll('input[type="date"]');
                dateInputs.forEach(function(input) {
                    const classes = input.className || '';
                    const required = input.required;
                    const placeholder = input.getAttribute('placeholder') || 'dd/MM/yyyy';
                    const defaultDate = input.value || null;
                    if (!input.hasAttribute('lang')) input.setAttribute('lang', 'es-VE');
                    input.setAttribute('aria-label', 'Formato de fecha dd/MM/yyyy');

                    flatpickr(input, {
                        locale: 'es',
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd/m/Y',
                        altInputClass: classes || 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                        allowInput: true,
                        defaultDate: defaultDate,
                        onReady: function(selectedDates, dateStr, instance) {
                            if (instance.altInput) {
                                instance.altInput.placeholder = placeholder;
                                if (required) instance.altInput.required = true;
                                instance.altInput.setAttribute('aria-label', 'Formato de fecha dd/MM/yyyy');
                            }
                        }
                    });
                });
            });
        </script>
    </body>
</html>
