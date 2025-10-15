<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Fondo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900">{{ $fondo->nombre }}</h3>
                <a href="{{ route('fondos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    ← Volver
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Saldo USD</p>
                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($fondo->saldo_usd, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Saldo Bs</p>
                    <p class="text-2xl font-semibold text-gray-900">Bs {{ number_format($fondo->saldo_bs, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Descripción</p>
                    <p class="text-gray-900">{{ $fondo->descripcion ?? '-' }}</p>
                </div>
            </div>

            @php($user = Auth::user())
            @php($isPropietario = $user && method_exists($user, 'isUsuarioPropietario') && $user->isUsuarioPropietario())
            @unless($isPropietario)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Formulario de Ingreso -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Registrar Ingreso</h4>
                            <form action="{{ route('fondos.ingreso', $fondo) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="ingreso_monto_usd" class="block text-sm font-medium text-gray-700 mb-1">Monto (USD)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number" name="monto_usd" id="ingreso_monto_usd" step="0.01" min="0" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="ingreso_monto_bs" class="block text-sm font-medium text-gray-700 mb-1">Monto (Bs)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                                            <input type="number" name="monto_bs" id="ingreso_monto_bs" step="0.01" min="0" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="ingreso_fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                    <input type="date" name="fecha" id="ingreso_fecha" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="ingreso_descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción (Opcional)</label>
                                    <textarea name="descripcion" id="ingreso_descripcion" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Registrar Ingreso
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Formulario de Egreso -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Registrar Egreso</h4>
                            <form action="{{ route('fondos.egreso', $fondo) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="egreso_monto_usd" class="block text-sm font-medium text-gray-700 mb-1">Monto (USD)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number" name="monto_usd" id="egreso_monto_usd" step="0.01" min="0" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="egreso_monto_bs" class="block text-sm font-medium text-gray-700 mb-1">Monto (Bs)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">Bs</span>
                                            <input type="number" name="monto_bs" id="egreso_monto_bs" step="0.01" min="0" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="egreso_fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                                    <input type="date" name="fecha" id="egreso_fecha" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="egreso_descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción (Opcional)</label>
                                    <textarea name="descripcion" id="egreso_descripcion" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Registrar Egreso
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endunless

            <!-- Tabla de movimientos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Movimientos del Fondo</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto USD</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Bs</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($fondo->movimientos as $movimiento)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($movimiento->fecha)->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $movimiento->tipo === 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst($movimiento->tipo) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($movimiento->monto_usd ?? 0, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Bs {{ number_format($movimiento->monto_bs ?? 0, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movimiento->descripcion ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @unless($isPropietario)
                                                <form action="{{ route('fondos.movimientos.destroy', $movimiento) }}" method="POST" onsubmit="return confirm('¿Eliminar este movimiento? Se ajustarán los saldos del fondo.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @else
                                                -
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay movimientos registrados para este fondo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>