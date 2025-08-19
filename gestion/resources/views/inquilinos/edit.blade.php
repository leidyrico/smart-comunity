<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Propietario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('inquilinos.update', $inquilino) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nombre del Propietario -->
                        <div class="mb-4">
                            <x-input-label for="nombre_inquilino" :value="__('Nombre del Propietario *')" />
                            <x-text-input id="nombre_inquilino" class="block mt-1 w-full" type="text" name="nombre_inquilino" :value="old('nombre_inquilino', $inquilino->nombre_inquilino)" required autofocus />
                            <x-input-error :messages="$errors->get('nombre_inquilino')" class="mt-2" />
                        </div>

                        <!-- Número de Apartamento -->
                        <div class="mb-4">
                            <x-input-label for="nro_apartamento" :value="__('Número de Apartamento *')" />
                            <x-text-input id="nro_apartamento" class="block mt-1 w-full" type="text" name="nro_apartamento" :value="old('nro_apartamento', $inquilino->nro_apartamento)" required />
                            <x-input-error :messages="$errors->get('nro_apartamento')" class="mt-2" />
                        </div>

                        <!-- Monto de Deuda -->
                        <div class="mb-4">
                            <x-input-label for="monto_deuda" :value="__('Monto de Deuda Actual *')" />
                            <x-text-input id="monto_deuda" class="block mt-1 w-full" type="number" step="0.01" min="0" name="monto_deuda" :value="old('monto_deuda', $inquilino->monto_deuda)" required />
                            <x-input-error :messages="$errors->get('monto_deuda')" class="mt-2" />
                        </div>

                        <!-- Monto Último Pago -->
                        <div class="mb-4">
                            <x-input-label for="monto_ultimo_pago" :value="__('Monto del Último Pago')" />
                            <x-text-input id="monto_ultimo_pago" class="block mt-1 w-full" type="number" step="0.01" min="0" name="monto_ultimo_pago" :value="old('monto_ultimo_pago', $inquilino->monto_ultimo_pago)" />
                            <x-input-error :messages="$errors->get('monto_ultimo_pago')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Si modifica este valor, se actualizará automáticamente la fecha del último pago.</p>
                        </div>

                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Información Actual:</h4>
                            <p class="text-xs text-gray-600">Fecha de la deuda: {{ $inquilino->fecha_deuda->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-600">Fecha último pago: {{ $inquilino->fecha_ultimo_pago ? $inquilino->fecha_ultimo_pago->format('d/m/Y H:i') : 'Sin pagos registrados' }}</p>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('inquilinos.show', $inquilino) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Propietario') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>