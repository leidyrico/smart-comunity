<x-app-with-sidebar>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Apartamentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Apartamentos</h3>
                        <div class="flex space-x-2">
                            <button onclick="printTable()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Imprimir
                            </button>

                            <a href="{{ route('apartamentos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Nuevo Apartamento
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <form method="GET" action="{{ route('apartamentos.index') }}" class="flex flex-wrap items-end gap-4">
                            <div class="flex-1 min-w-48">
                                <label for="estatus_financiero" class="block text-sm font-medium text-gray-700 mb-1">Estatus Financiero</label>
                                <select name="estatus_financiero" id="estatus_financiero" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Todos los estatus</option>
                                    <option value="solvente" {{ request('estatus_financiero') === 'solvente' ? 'selected' : '' }}>Solvente</option>
                                    <option value="deudor" {{ request('estatus_financiero') === 'deudor' ? 'selected' : '' }}>Deudor</option>
                                    <option value="moroso" {{ request('estatus_financiero') === 'moroso' ? 'selected' : '' }}>Moroso</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Filtrar
                                </button>
                                @if(request()->hasAny(['estatus_financiero']))
                                    <a href="{{ route('apartamentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Limpiar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    @if($apartamentos->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piso</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus Financiero</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Pendiente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($apartamentos as $apartamento)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $apartamento->numero }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $apartamento->piso }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $apartamento->propietario }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $apartamento->email ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    {{ $apartamento->estado === 'ocupado' ? 'bg-green-100 text-green-800' : 
                                                       ($apartamento->estado === 'desocupado' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($apartamento->estado) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    @if($apartamento->estatus_financiero === 'solvente')
                                                        bg-green-100 text-green-800
                                                    @elseif($apartamento->estatus_financiero === 'deudor')
                                                        bg-yellow-100 text-yellow-800
                                                    @else
                                                        bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst($apartamento->estatus_financiero) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="font-medium {{ $apartamento->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                    ${{ number_format($apartamento->saldo_pendiente, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3 no-print">
                                <a href="{{ route('apartamentos.edit', $apartamento) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('deudas.index') }}?numero_apartamento={{ $apartamento->numero }}&nombre_propietario=&estado_deuda=&numero_recibo=" class="text-orange-600 hover:text-orange-900" title="Ver Deudas">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </a>
                                <button onclick="openDeleteModal({{ $apartamento->id }}, '{{ $apartamento->numero }}')" class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay apartamentos registrados</h3>
                            <p class="mt-1 text-sm text-gray-500">Comience creando un nuevo apartamento.</p>
                            <div class="mt-6">
                                <a href="{{ route('apartamentos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Nuevo Apartamento
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-with-sidebar>

<!-- Modal de Confirmación para Eliminar -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirmar Eliminación</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="deleteMessage">
                    ¿Está seguro de que desea eliminar el apartamento <span id="apartmentNumber"></span>?
                </p>
                <div class="mt-4">
                    <label for="adminPassword" class="block text-sm font-medium text-gray-700 mb-2">
                        Clave de Administrador:
                    </label>
                    <input type="password" id="adminPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Ingrese la clave de administrador">
                    <div id="passwordError" class="text-red-600 text-sm mt-1 hidden">Clave de administrador incorrecta</div>
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex space-x-3">
                    <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Eliminar
                    </button>
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body * {
        visibility: hidden;
    }
    
    .print-area, .print-area * {
        visibility: visible;
    }
    
    .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    table {
        width: 100% !important;
        border-collapse: collapse;
    }
    
    th, td {
        border: 1px solid #000 !important;
        padding: 8px !important;
        text-align: left;
    }
    
    th {
        background-color: #f3f4f6 !important;
        font-weight: bold;
    }
}
</style>

<script>
let currentApartmentId = null;

function openDeleteModal(apartmentId, apartmentNumber) {
    currentApartmentId = apartmentId;
    document.getElementById('apartmentNumber').textContent = apartmentNumber;
    document.getElementById('adminPassword').value = '';
    document.getElementById('passwordError').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('hidden');
    
    // Focus en el campo de contraseña
    setTimeout(() => {
        document.getElementById('adminPassword').focus();
    }, 100);
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentApartmentId = null;
}

function deleteApartment() {
    const password = document.getElementById('adminPassword').value;
    const errorDiv = document.getElementById('passwordError');
    
    if (!password) {
        errorDiv.textContent = 'Por favor ingrese la clave de administrador';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Crear formulario para enviar la eliminación
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/apartamentos/${currentApartmentId}`;
    
    // Agregar token CSRF
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Agregar método DELETE
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Agregar clave de administrador
    const passwordField = document.createElement('input');
    passwordField.type = 'hidden';
    passwordField.name = 'admin_password';
    passwordField.value = password;
    form.appendChild(passwordField);
    
    // Enviar formulario
    document.body.appendChild(form);
    form.submit();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Botón confirmar eliminación
    document.getElementById('confirmDelete').addEventListener('click', deleteApartment);
    
    // Botón cancelar
    document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
    
    // Enter en el campo de contraseña
    document.getElementById('adminPassword').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            deleteApartment();
        }
    });
    
    // Escape para cerrar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !document.getElementById('deleteModal').classList.contains('hidden')) {
            closeDeleteModal();
        }
    });
});

function printTable() {
    // Crear una copia de la tabla sin la columna de acciones
    const originalTable = document.querySelector('table');
    if (!originalTable) {
        alert('No se encontró la tabla para imprimir');
        return;
    }
    
    const tableClone = originalTable.cloneNode(true);
    
    // Remover la columna de acciones (última columna)
    const headerRow = tableClone.querySelector('thead tr');
    if (headerRow) {
        const lastHeader = headerRow.querySelector('th:last-child');
        if (lastHeader) lastHeader.remove();
    }
    
    const bodyRows = tableClone.querySelectorAll('tbody tr');
    bodyRows.forEach(row => {
        const lastCell = row.querySelector('td:last-child');
        if (lastCell) lastCell.remove();
    });
    
    // Crear ventana de impresión
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Lista de Apartamentos - Residencias Alfa</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                h1 {
                    text-align: center;
                    color: #333;
                    margin-bottom: 30px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 12px;
                    text-align: left;
                }
                th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                    color: #333;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .text-center {
                    text-align: center;
                }
                .print-date {
                    text-align: right;
                    margin-bottom: 20px;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="print-date">Fecha de impresión: ${new Date().toLocaleDateString('es-ES')}</div>
            <h1>Lista de Apartamentos - Residencias Alfa</h1>
            ${tableClone.outerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Esperar a que se cargue el contenido antes de imprimir
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}
</script>