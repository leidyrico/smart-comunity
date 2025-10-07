<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Inventario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Lista de Inventario</h3>
                        <div class="flex space-x-2">
                            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Imprimir
                            </button>
                            <a href="{{ route('inventario.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Nuevo Elemento
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Formulario de Filtros -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <form method="GET" action="{{ route('inventario.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Filtro por Búsqueda -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Buscar por nombre o descripción..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Filtro por Categoría -->
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                                <select id="categoria" 
                                        name="categoria"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria }}" {{ request('categoria') == $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select id="estado" 
                                        name="estado"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Todos los estados</option>
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Filtrar
                                </button>
                                <a href="{{ route('inventario.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de Inventario -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($inventarios as $inventario)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $inventario->nombre }}</div>
                                            @if($inventario->descripcion)
                                                <div class="text-sm text-gray-500">{{ Str::limit($inventario->descripcion, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $inventario->categoria ?? 'Sin categoría' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $inventario->cantidad }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($inventario->estado == 'disponible') bg-green-100 text-green-800
                                                @elseif($inventario->estado == 'en_uso') bg-blue-100 text-blue-800
                                                @elseif($inventario->estado == 'mantenimiento') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $inventario->estado)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $inventario->ubicacion ?? 'No especificada' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('inventario.show', $inventario) }}" 
                                                   title="Ver detalles" 
                                                   class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('inventario.edit', $inventario) }}" 
                                                   title="Editar elemento" 
                                                   class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-800 rounded-full hover:bg-yellow-200 transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <button onclick="openDeleteModal({{ $inventario->id }}, '{{ $inventario->nombre }}')" 
                                                        title="Eliminar elemento" 
                                                        class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-800 rounded-full hover:bg-red-200 transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No se encontraron elementos en el inventario.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($inventarios->hasPages())
                        <div class="mt-6">
                            {{ $inventarios->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <style>
        @media print {
            /* Ocultar botones de acción */
            .flex.justify-between.items-center.mb-6 {
                display: none !important;
            }
            
            /* Ocultar formulario de filtros */
            .bg-gray-50.p-4.rounded-lg.mb-6 {
                display: none !important;
            }
            
            /* Ocultar columna de acciones en la tabla */
            th:last-child,
            td:last-child {
                display: none !important;
            }
            
            /* Ocultar paginación */
            .mt-6 {
                display: none !important;
            }
            
            /* Ajustar el diseño para impresión */
            body {
                font-size: 12px;
            }
            
            .max-w-7xl {
                max-width: none;
            }
            
            .shadow-sm {
                box-shadow: none;
            }
        }
     </style>

     <!-- Modal de Confirmación de Eliminación -->
     <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
         <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
             <div class="mt-3 text-center">
                 <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                     <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                     </svg>
                 </div>
                 <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar Eliminación</h3>
                 <div class="mt-2 px-7 py-3">
                     <p class="text-sm text-gray-500" id="deleteMessage">
                         ¿Está seguro de que desea eliminar este elemento?
                     </p>
                 </div>
                 <form id="deleteForm" method="POST" class="mt-4">
                     @csrf
                     @method('DELETE')
                     <div class="mb-4">
                         <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">
                             Clave de Administrador:
                         </label>
                         <input type="password" 
                                id="admin_password" 
                                name="admin_password" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                placeholder="Ingrese la clave de administrador">
                     </div>
                     <div class="flex justify-center space-x-4">
                         <button type="button" 
                                 onclick="closeDeleteModal()"
                                 class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                             Cancelar
                         </button>
                         <button type="submit" 
                                 class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                             Eliminar
                         </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>

     <script>
         function openDeleteModal(inventarioId, inventarioNombre) {
             document.getElementById('deleteMessage').textContent = 
                 `¿Está seguro de que desea eliminar el elemento "${inventarioNombre}"?`;
             
             document.getElementById('deleteForm').action = 
                 `/inventario/${inventarioId}`;
             
             document.getElementById('admin_password').value = '';
             document.getElementById('deleteModal').classList.remove('hidden');
         }

         function closeDeleteModal() {
             document.getElementById('deleteModal').classList.add('hidden');
         }

         // Cerrar modal al hacer clic fuera de él
         document.getElementById('deleteModal').addEventListener('click', function(e) {
             if (e.target === this) {
                 closeDeleteModal();
             }
         });

         // Cerrar modal con tecla Escape
         document.addEventListener('keydown', function(e) {
             if (e.key === 'Escape') {
                 closeDeleteModal();
             }
         });
     </script>
 </x-app-with-sidebar>