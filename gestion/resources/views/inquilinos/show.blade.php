<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Propietario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensajes de éxito -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información del Inquilino -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Propietario</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Nombre:</span>
                                <p class="text-sm text-gray-900">{{ $inquilino->nombre_inquilino }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Apartamento:</span>
                                <p class="text-sm text-gray-900">{{ $inquilino->nro_apartamento }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Deuda Actual:</span>
                                <p class="text-lg font-semibold {{ $inquilino->monto_deuda > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    ${{ number_format($inquilino->monto_deuda, 2) }}
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Fecha de la Deuda:</span>
                                <p class="text-sm text-gray-900">{{ $inquilino->fecha_deuda->format('d/m/Y') }}</p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Último Pago:</span>
                                <p class="text-sm text-gray-900">
                                    {{ $inquilino->monto_ultimo_pago ? '$' . number_format($inquilino->monto_ultimo_pago, 2) : 'Sin pagos registrados' }}
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Fecha Último Pago:</span>
                                <p class="text-sm text-gray-900">
                                    {{ $inquilino->fecha_ultimo_pago ? $inquilino->fecha_ultimo_pago->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Estado:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $inquilino->monto_deuda > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $inquilino->monto_deuda > 0 ? 'Con Deuda' : 'Al Día' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex space-x-3">
                            <a href="{{ route('inquilinos.edit', $inquilino) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Editar
                            </a>
                            <a href="{{ route('inquilinos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Volver
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Procesar Pago -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Procesar Pago</h3>
                        
                        @if($inquilino->monto_deuda > 0)
                            <form method="POST" action="{{ route('inquilinos.pago', $inquilino) }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <x-input-label for="monto_pago" :value="__('Monto del Pago *')" />
                                    <x-text-input id="monto_pago" class="block mt-1 w-full" type="number" step="0.01" min="0.01" max="{{ $inquilino->monto_deuda }}" name="monto_pago" :value="old('monto_pago')" required />
                                    <x-input-error :messages="$errors->get('monto_pago')" class="mt-2" />
                                    <p class="text-xs text-gray-500 mt-1">Máximo: ${{ number_format($inquilino->monto_deuda, 2) }}</p>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                    <h4 class="text-sm font-medium text-blue-800 mb-2">Información del Pago:</h4>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li>• Se actualizará automáticamente la fecha del último pago</li>
                                        <li>• El monto se restará de la deuda actual</li>
                                        <li>• Si el pago cubre toda la deuda, el estado cambiará a "Al Día"</li>
                                    </ul>
                                </div>
                                
                                <x-primary-button class="w-full justify-center">
                                    {{ __('Procesar Pago') }}
                                </x-primary-button>
                            </form>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin Deuda Pendiente</h3>
                                <p class="mt-1 text-sm text-gray-500">Este inquilino está al día con sus pagos.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>