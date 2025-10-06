<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Egreso') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Botón de regreso -->
                    <div class="mb-6">
                        <a href="{{ route('egresos.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← Volver a la lista
                        </a>
                    </div>

                    <!-- Formulario de creación -->
                    <form action="{{ route('egresos.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Número de Factura -->
                        <div>
                            <label for="nro_factura" class="block text-sm font-medium text-gray-700 mb-1">
                                Número de Factura <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nro_factura" 
                                   id="nro_factura" 
                                   value="{{ old('nro_factura') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nro_factura') border-red-500 @enderror">
                            @error('nro_factura')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Número de Comprobante -->
                        <div>
                            <label for="comprobante" class="block text-sm font-medium text-gray-700 mb-1">
                                Número de Comprobante
                            </label>
                            <input type="text" 
                                   name="comprobante" 
                                   id="comprobante" 
                                   value="{{ old('comprobante') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('comprobante') border-red-500 @enderror">
                            @error('comprobante')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
                                Fecha <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="fecha" 
                                   id="fecha" 
                                   value="{{ old('fecha', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('fecha') border-red-500 @enderror">
                            @error('fecha')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Proveedor -->
                        <div>
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Proveedor <span class="text-red-500">*</span>
                            </label>
                            <select name="proveedor_id" 
                                    id="proveedor_id" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('proveedor_id') border-red-500 @enderror">
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" 
                                            {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                        {{ $proveedor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proveedor_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Enlace para crear nuevo proveedor -->
                            <p class="mt-1 text-sm text-gray-600">
                                ¿No encuentra el proveedor? 
                                <a href="{{ route('proveedores.create') }}" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 underline">
                                    Crear nuevo proveedor
                                </a>
                            </p>
                        </div>

                        <!-- Monto en USD -->
                        <div>
                            <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">
                                Monto (USD) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" 
                                       name="monto" 
                                       id="monto" 
                                       value="{{ old('monto') }}"
                                       step="0.01"
                                       min="0"
                                       required
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('monto') border-red-500 @enderror">
                            </div>
                            @error('monto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Monto en Bolívares (opcional) -->
                        <div>
                            <label for="monto_en_bs" class="block text-sm font-medium text-gray-700 mb-1">
                                Monto en Bolívares (opcional)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                                <input type="number" 
                                       name="monto_en_bs" 
                                       id="monto_en_bs" 
                                       value="{{ old('monto_en_bs') }}"
                                       step="0.01"
                                       min="0"
                                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('monto_en_bs') border-red-500 @enderror">
                            </div>
                            @error('monto_en_bs')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="descripcion" 
                                      id="descripcion" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3 pt-6">
                            <a href="{{ route('egresos.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Crear Egreso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para actualizar lista de proveedores si se abre en nueva ventana -->
    <script>
        // Escuchar cuando se cierre una ventana de crear proveedor
        window.addEventListener('focus', function() {
            // Recargar la página para actualizar la lista de proveedores
            // Solo si hay un parámetro que indique que se creó un proveedor
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('refresh') === 'proveedores') {
                location.reload();
            }
        });
    </script>
</x-app-with-sidebar>