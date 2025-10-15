<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Consultar Documentos') }}
        </h2>
    </x-slot>

    @php($isPropietario = Auth::check() && Auth::user()->isUsuarioPropietario())

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Lista de Documentos</h3>
                        @unless($isPropietario)
                            <a href="{{ route('actas.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Nuevo Documento
                            </a>
                        @endunless
                    </div>

                    <!-- Formulario de Filtros -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <form method="GET" action="{{ route('actas.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Filtro por Número de Documento -->
                            <div>
                                <label for="nro_documento" class="block text-sm font-medium text-gray-700 mb-1">Nro. Documento</label>
                                <input type="text" 
                                       id="nro_documento" 
                                       name="nro_documento" 
                                       value="{{ request('nro_documento') }}"
                                       placeholder="Buscar por número..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Filtro por Tipo de Documento -->
                            <div>
                                <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento</label>
                                <select id="tipo_documento" 
                                        name="tipo_documento"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Todos los tipos</option>
                                    <option value="Correspondencia" {{ request('tipo_documento') == 'Correspondencia' ? 'selected' : '' }}>Correspondencia</option>
                                    <option value="Comunicado" {{ request('tipo_documento') == 'Comunicado' ? 'selected' : '' }}>Comunicado</option>
                                    <option value="Actas" {{ request('tipo_documento') == 'Actas' ? 'selected' : '' }}>Actas</option>
                                </select>
                            </div>

                            <!-- Filtro por Fecha Desde -->
                            <div>
                                <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                                <input type="date" 
                                       id="fecha_desde" 
                                       name="fecha_desde" 
                                       value="{{ request('fecha_desde') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Filtro por Fecha Hasta -->
                            <div>
                                <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                                <input type="date" 
                                       id="fecha_hasta" 
                                       name="fecha_hasta" 
                                       value="{{ request('fecha_hasta') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Botones de Acción -->
                            <div class="lg:col-span-4 flex justify-start space-x-3 mt-2">
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Filtrar
                                </button>
                                <a href="{{ route('actas.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Limpiar
                                </a>
                            </div>
                        </form>
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

                    @if($actas->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nro. Documento
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo de Documento
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Descripción
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($actas as $acta)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $acta->nro_doc }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $acta->nombre_doc }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                    {{ $acta->tipo_documento ?? 'Actas' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $acta->fecha->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="max-w-xs truncate" title="{{ $acta->descripcion }}">
                                                    {{ Str::limit($acta->descripcion, 50) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    @if($acta->archivo_nombre)
                                                        <a href="{{ route('actas.download', $acta) }}" class="inline-flex items-center justify-center w-8 h-8 bg-green-600 border border-transparent rounded text-white hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-1 focus:ring-green-500 focus:ring-offset-1 transition ease-in-out duration-150" title="Descargar documento">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                    @unless($isPropietario)
                                                        <button onclick="openDeleteModal({{ $acta->id }}, {{ json_encode($acta->nombre_doc) }})" class="inline-flex items-center justify-center w-8 h-8 bg-red-600 border border-transparent rounded text-white hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-1 focus:ring-red-500 focus:ring-offset-1 transition ease-in-out duration-150" title="Eliminar documento">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @endunless
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay documentos registrados</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer documento.</p>
                            <div class="mt-6">
                                @unless($isPropietario)
                                    <a href="{{ route('actas.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        Nuevo Documento
                                    </a>
                                @endunless
                            </div>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @unless($isPropietario)
    <!-- Modal de Confirmación para Eliminar Documento -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Eliminar Documento</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        ¿Está seguro que desea eliminar el documento <strong id="documentName"></strong>?
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="mb-4">
                        <label for="adminPassword" class="block text-sm font-medium text-gray-700 mb-2">
                            Clave de Administrador *
                        </label>
                        <input type="password" 
                               id="adminPassword" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                               placeholder="Ingrese la clave de administrador">
                        <div id="passwordError" class="text-red-600 text-sm mt-1 hidden"></div>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <div class="flex justify-center space-x-3">
                        <button id="cancelDelete" 
                                class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button id="confirmDelete" 
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
         </div>
     </div>

     <script>
         let documentToDelete = null;

         function openDeleteModal(id, name) {
             documentToDelete = id;
             document.getElementById('documentName').textContent = name;
             document.getElementById('adminPassword').value = '';
             document.getElementById('passwordError').classList.add('hidden');
             document.getElementById('deleteModal').classList.remove('hidden');
         }

         function closeDeleteModal() {
             document.getElementById('deleteModal').classList.add('hidden');
             documentToDelete = null;
         }

         // Event listeners
         document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);

         document.getElementById('confirmDelete').addEventListener('click', function() {
             const password = document.getElementById('adminPassword').value;
             const errorDiv = document.getElementById('passwordError');

             if (!password) {
                 errorDiv.textContent = 'La clave de administrador es requerida';
                 errorDiv.classList.remove('hidden');
                 return;
             }

             // Crear formulario para enviar la solicitud DELETE
             const form = document.createElement('form');
             form.method = 'POST';
             form.action = `{{ url('/actas') }}/${documentToDelete}`;

             // Token CSRF
             const csrfToken = document.createElement('input');
             csrfToken.type = 'hidden';
             csrfToken.name = '_token';
             csrfToken.value = '{{ csrf_token() }}';
             form.appendChild(csrfToken);

             // Método DELETE
             const methodField = document.createElement('input');
             methodField.type = 'hidden';
             methodField.name = '_method';
             methodField.value = 'DELETE';
             form.appendChild(methodField);

             // Clave de administrador
             const passwordField = document.createElement('input');
             passwordField.type = 'hidden';
             passwordField.name = 'admin_password';
             passwordField.value = password;
             form.appendChild(passwordField);

             document.body.appendChild(form);
             form.submit();
         });

         // Cerrar modal al hacer clic fuera de él
         document.getElementById('deleteModal').addEventListener('click', function(e) {
             if (e.target === this) {
                 closeDeleteModal();
             }
         });

         // Cerrar modal con tecla Escape
         document.addEventListener('keydown', function(e) {
             if (e.key === 'Escape' && !document.getElementById('deleteModal').classList.contains('hidden')) {
                 closeDeleteModal();
             }
         });
     </script>
     @endunless
 </x-app-with-sidebar>