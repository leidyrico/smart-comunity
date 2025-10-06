<x-app-with-sidebar>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Recaudación</h1>
                <p class="mt-2 text-gray-600">Consulta cuántos apartamentos han pagado cada recibo</p>
            </div>

            <!-- Filtro de Recibos (Oculto) -->
            <div class="bg-white shadow rounded-lg p-6 mb-6 hidden">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtrar Recibos</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="filtro-numero" class="block text-sm font-medium text-gray-700 mb-2">Número de Recibo</label>
                        <input type="text" id="filtro-numero" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: 001">
                    </div>
                    <div>
                        <label for="filtro-periodo" class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                        <input type="month" id="filtro-periodo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="filtrarRecibos()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de Recibos -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recibos Emitidos</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="tabla-recibos">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Apartamentos Asignados</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apartamentos Pagados</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Recaudación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tbody-recibos">
                            <!-- Los datos se cargarán aquí via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detalles del Recibo Seleccionado -->
            <div id="detalle-recibo" class="hidden bg-white shadow rounded-lg p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles del Recibo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-blue-600">Período</div>
                        <div id="detalle-periodo" class="text-lg font-semibold text-blue-900">-</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-green-600">Fecha Emisión</div>
                        <div id="detalle-emision" class="text-lg font-semibold text-green-900">-</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-yellow-600">Fecha Vencimiento</div>
                        <div id="detalle-vencimiento" class="text-lg font-semibold text-yellow-900">-</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-purple-600">Monto Total</div>
                        <div id="detalle-monto" class="text-lg font-semibold text-purple-900">-</div>
                    </div>
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-indigo-600">Apartamentos Pagados</div>
                        <div id="detalle-pagados" class="text-lg font-semibold text-indigo-900">-</div>
                    </div>
                </div>

                <!-- Lista de Apartamentos que Pagaron -->
                <div class="mt-6">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Apartamentos que Pagaron</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apartamento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Pagado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-pagos" class="bg-white divide-y divide-gray-200">
                                <!-- Los datos se cargarán aquí via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación de Recibos -->
                    <div id="paginacion-recibos" class="px-6 py-4 border-t border-gray-200" style="display: none;">
                        <div class="flex items-center justify-between">
                            <div id="info-paginacion" class="text-sm text-gray-700"></div>
                            <div id="botones-paginacion" class="flex space-x-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Cargar recibos al inicializar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarRecibos();
        });

        // Función para cargar todos los recibos
        function cargarRecibos() {
            fetch('{{ url("api/recaudacion/recibos") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    // Verificar si la respuesta es JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => {
                            // Si es una respuesta JSON de error de autenticación
                            if (response.status === 401 && data.error) {
                                throw new Error('authentication_required');
                            }
                            // Si la respuesta no es exitosa pero es JSON
                            if (!response.ok) {
                                throw new Error(data.message || `HTTP error! status: ${response.status}`);
                            }
                            return data;
                        });
                    } else {
                        // Si no es JSON, verificar si es una redirección al login
                        if (!response.ok) {
                            if (response.url.includes('/login')) {
                                throw new Error('authentication_required');
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        throw new Error('La respuesta no es JSON válido.');
                    }
                })
                .then(data => {
                    if (data) {
                        mostrarRecibos(data);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar recibos:', error);
                    
                    // Si el error indica problema de autenticación, redirigir al login
                    if (error.message === 'authentication_required') {
                        mostrarError('Sesión expirada. Redirigiendo al login...');
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    } else {
                        mostrarError('Error al cargar los recibos. Por favor, intente nuevamente.');
                    }
                });
        }

        // Función para filtrar recibos
        function filtrarRecibos() {
            const numero = document.getElementById('filtro-numero').value;
            const periodo = document.getElementById('filtro-periodo').value;
            
            let url = '/api/recaudacion/recibos?';
            const params = new URLSearchParams();
            
            if (numero) params.append('numero', numero);
            if (periodo) params.append('periodo', periodo);
            
            url += params.toString();
            
            fetch(url)
                .then(response => {
                    // Verificar si la respuesta es exitosa
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    // Verificar si la respuesta es JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        // Si no es JSON, probablemente es una redirección al login
                        if (response.url.includes('/login')) {
                            window.location.href = '/login';
                            return;
                        }
                        throw new Error('La respuesta no es JSON válido. Posible problema de autenticación.');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        mostrarRecibos(data);
                    }
                })
                .catch(error => {
                    console.error('Error al filtrar recibos:', error);
                    
                    // Si el error indica problema de autenticación, redirigir al login
                    if (error.message.includes('autenticación') || error.message.includes('JSON')) {
                        mostrarError('Sesión expirada. Redirigiendo al login...');
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    } else {
                        mostrarError('Error al filtrar los recibos. Por favor, intente nuevamente.');
                    }
                });
        }

        // Función para mostrar recibos en la tabla
        function mostrarRecibos(data) {
            const tbody = document.getElementById('tbody-recibos');
            tbody.innerHTML = '';

            // Si data es un array (respuesta antigua), convertir al nuevo formato
            let recibos, paginacion;
            if (Array.isArray(data)) {
                recibos = data;
                paginacion = null;
            } else {
                recibos = data.data || [];
                paginacion = data;
            }

            if (recibos.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron recibos
                        </td>
                    </tr>
                `;
                // Ocultar paginación si no hay datos
                document.getElementById('paginacion-recibos').style.display = 'none';
                return;
            }

            recibos.forEach(recibo => {
                const porcentaje = recibo.total_apartamentos > 0 
                    ? Math.round((recibo.apartamentos_pagados / recibo.total_apartamentos) * 100) 
                    : 0;
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${recibo.numero}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${recibo.periodo}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${recibo.total_apartamentos}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${recibo.apartamentos_pagados}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex items-center">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${porcentaje}%"></div>
                            </div>
                            <span>${porcentaje}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="verDetalle(${recibo.id})" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye mr-1"></i>Ver Detalle
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Mostrar paginación si existe
            if (paginacion && paginacion.last_page > 1) {
                mostrarPaginacion(paginacion);
            } else {
                document.getElementById('paginacion-recibos').style.display = 'none';
            }
        }

        // Función para mostrar la paginación
        function mostrarPaginacion(paginacion) {
            const contenedorPaginacion = document.getElementById('paginacion-recibos');
            const infoPaginacion = document.getElementById('info-paginacion');
            const botonesPaginacion = document.getElementById('botones-paginacion');

            // Mostrar información de paginación
            infoPaginacion.textContent = `Mostrando ${paginacion.from} a ${paginacion.to} de ${paginacion.total} recibos`;

            // Limpiar botones existentes
            botonesPaginacion.innerHTML = '';

            // Botón Anterior
            if (paginacion.current_page > 1) {
                const btnAnterior = document.createElement('a');
                btnAnterior.href = '#';
                btnAnterior.className = 'px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50';
                btnAnterior.textContent = 'Anterior';
                btnAnterior.onclick = (e) => {
                    e.preventDefault();
                    cargarPagina(paginacion.current_page - 1);
                };
                botonesPaginacion.appendChild(btnAnterior);
            } else {
                const btnAnterior = document.createElement('span');
                btnAnterior.className = 'px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed';
                btnAnterior.textContent = 'Anterior';
                botonesPaginacion.appendChild(btnAnterior);
            }

            // Números de página
            for (let i = 1; i <= paginacion.last_page; i++) {
                if (i === paginacion.current_page) {
                    const spanActual = document.createElement('span');
                    spanActual.className = 'px-3 py-2 text-sm text-white bg-blue-600 rounded-md';
                    spanActual.textContent = i;
                    botonesPaginacion.appendChild(spanActual);
                } else {
                    const btnPagina = document.createElement('a');
                    btnPagina.href = '#';
                    btnPagina.className = 'px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50';
                    btnPagina.textContent = i;
                    btnPagina.onclick = (e) => {
                        e.preventDefault();
                        cargarPagina(i);
                    };
                    botonesPaginacion.appendChild(btnPagina);
                }
            }

            // Botón Siguiente
            if (paginacion.current_page < paginacion.last_page) {
                const btnSiguiente = document.createElement('a');
                btnSiguiente.href = '#';
                btnSiguiente.className = 'px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50';
                btnSiguiente.textContent = 'Siguiente';
                btnSiguiente.onclick = (e) => {
                    e.preventDefault();
                    cargarPagina(paginacion.current_page + 1);
                };
                botonesPaginacion.appendChild(btnSiguiente);
            } else {
                const btnSiguiente = document.createElement('span');
                btnSiguiente.className = 'px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed';
                btnSiguiente.textContent = 'Siguiente';
                botonesPaginacion.appendChild(btnSiguiente);
            }

            // Mostrar el contenedor de paginación
            contenedorPaginacion.style.display = 'block';
        }

        // Función para cargar una página específica
        function cargarPagina(pagina) {
            const numero = document.getElementById('filtro-numero').value;
            const periodo = document.getElementById('filtro-periodo').value;
            
            let url = `/api/recaudacion/recibos?page=${pagina}`;
            const params = new URLSearchParams();
            
            if (numero) params.append('numero', numero);
            if (periodo) params.append('periodo', periodo);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        if (response.url.includes('/login')) {
                            window.location.href = '/login';
                            return;
                        }
                        throw new Error('La respuesta no es JSON válido.');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        mostrarRecibos(data);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar página:', error);
                    mostrarError('Error al cargar los recibos. Por favor, intente nuevamente.');
                });
        }

        // Función para ver detalle de un recibo
        function verDetalle(reciboId) {
            fetch(`{{ url("api/recaudacion/detalle") }}/${reciboId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    // Verificar si la respuesta es JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => {
                            // Si es una respuesta JSON de error de autenticación
                            if (response.status === 401 && data.error) {
                                throw new Error('authentication_required');
                            }
                            // Si la respuesta no es exitosa pero es JSON
                            if (!response.ok) {
                                throw new Error(data.message || `HTTP error! status: ${response.status}`);
                            }
                            return data;
                        });
                    } else {
                        // Si no es JSON, verificar si es una redirección al login
                        if (!response.ok) {
                            if (response.url.includes('/login')) {
                                throw new Error('authentication_required');
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        throw new Error('La respuesta no es JSON válido.');
                    }
                })
                .then(data => {
                    if (data) {
                        mostrarDetalle(data);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar detalle:', error);
                    
                    // Si el error indica problema de autenticación, redirigir al login
                    if (error.message === 'authentication_required') {
                        mostrarError('Sesión expirada. Redirigiendo al login...');
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    } else {
                        mostrarError('Error al cargar el detalle del recibo. Por favor, intente nuevamente.');
                    }
                });
        }

        // Función para mostrar el detalle del recibo
        function mostrarDetalle(data) {
            // Mostrar información general del recibo
            document.getElementById('detalle-periodo').textContent = data.recibo.periodo;
            document.getElementById('detalle-emision').textContent = formatearFecha(data.recibo.fecha_emision);
            document.getElementById('detalle-vencimiento').textContent = formatearFecha(data.recibo.fecha_vencimiento);
            document.getElementById('detalle-monto').textContent = formatearMoneda(data.recibo.monto_total);
            document.getElementById('detalle-pagados').textContent = `${data.pagos.length} de ${data.recibo.total_apartamentos}`;

            // Mostrar lista de pagos
            const tbodyPagos = document.getElementById('tbody-pagos');
            tbodyPagos.innerHTML = '';

            if (data.pagos.length === 0) {
                tbodyPagos.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No hay pagos registrados para este recibo
                        </td>
                    </tr>
                `;
            } else {
                data.pagos.forEach(pago => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${pago.apartamento}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pago.propietario}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatearMoneda(pago.monto)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatearFecha(pago.fecha_pago)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${pago.metodo_pago}</td>
                    `;
                    tbodyPagos.appendChild(row);
                });
            }

            // Mostrar la sección de detalles
            document.getElementById('detalle-recibo').classList.remove('hidden');
            
            // Scroll suave hacia la sección de detalles
            document.getElementById('detalle-recibo').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Función para formatear fechas
        function formatearFecha(fecha) {
            if (!fecha) return '-';
            return new Date(fecha).toLocaleDateString('es-ES');
        }

        // Función para formatear moneda
        function formatearMoneda(monto) {
            if (!monto) return '-';
            return new Intl.NumberFormat('es-US', {
                style: 'currency',
                currency: 'USD'
            }).format(monto);
        }

        // Función para mostrar errores
        function mostrarError(mensaje) {
            // Crear un toast de error simple
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = mensaje;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    </script>
    @endpush
</x-app-with-sidebar>