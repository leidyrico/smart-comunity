<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Inquilinos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container mx-auto px-4">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Importar Inquilinos desde CSV</h2>
        
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="font-semibold text-blue-800 mb-2">Instrucciones:</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li> El archivo debe ser formato CSV</li>
                <li> Columnas: nombre_inquilino, nro_apartamento, monto_deuda, monto_ultimo_pago, fecha_ultimo_pago</li>
                <li> La primera fila debe contener los encabezados</li>
                <li> Formato de fecha: YYYY-MM-DD (opcional)</li>
                <li> Los montos deben ser números decimales</li>
            </ul>
        </div>
        
        <div class="mb-4">
            <a href="{{ route('inquilinos.template') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Descargar Plantilla
            </a>
        </div>
        
        <form action="{{ route('inquilinos.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Seleccionar archivo CSV</label>
                <input type="file" name="file" id="file" accept=".csv,.txt" required
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    Importar Inquilinos
                </button>
                <a href="{{ route('inquilinos.index') }}" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>