<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Fondo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('fondos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            ← Volver a la lista
                        </a>
                    </div>

                    <form action="{{ route('fondos.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Fondo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror">
                            @error('nombre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción (Opcional)</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="saldo_usd" class="block text-sm font-medium text-gray-700 mb-1">Saldo Inicial (USD)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                                    <input type="number" name="saldo_usd" id="saldo_usd" value="{{ old('saldo_usd') }}" step="0.01" min="0" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('saldo_usd') border-red-500 @enderror">
                                </div>
                                @error('saldo_usd')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="saldo_bs" class="block text-sm font-medium text-gray-700 mb-1">Saldo Inicial (Bs)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                                    <input type="number" name="saldo_bs" id="saldo_bs" value="{{ old('saldo_bs') }}" step="0.01" min="0" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('saldo_bs') border-red-500 @enderror">
                                </div>
                                @error('saldo_bs')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6">
                            <a href="{{ route('fondos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancelar</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear Fondo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>