<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Dashboard - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: #1f2937;
            line-height: 1.2;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 210mm;
            margin: 0;
            background: white;
            padding: 0;
        }

        /* Header profesional */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 8px;
            position: relative;
            overflow: hidden;
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .logo {
            width: 16px;
            height: 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo svg {
            width: 12px;
            height: 12px;
            color: white;
        }

        .header-text h1 {
            font-size: 12px;
            font-weight: 700;
            margin: 0 0 1px 0;
        }

        .header-text p {
            font-size: 8px;
            margin: 0;
            opacity: 0.9;
        }

        .date-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 6px;
            border-radius: 3px;
        }

        .date-badge p {
            margin: 0;
            font-size: 8px;
            font-weight: 600;
        }

        /* Contenido principal */
        .main-content {
            padding: 4px 8px;
        }

        /* Sección de resumen ejecutivo */
        .executive-summary {
            text-align: center;
            margin-bottom: 4px;
            padding: 4px;
            background: #f8fafc;
            border-radius: 3px;
        }

        .summary-title {
            font-size: 10px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 2px 0;
        }

        /* Grid de estadísticas principales */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2px;
            margin-bottom: 3px;
        }

        /* Grid de secciones principales */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2px;
            margin-bottom: 3px;
        }

        /* Cards mejoradas */
        .card {
            background: white;
            border-radius: 3px;
            padding: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        /* Estadísticas mejoradas */
        .stat-card {
            background: white;
            border-radius: 3px;
            padding: 4px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .stat-card.primary {
            border-top: 2px solid #3b82f6;
        }

        .stat-card.danger {
            border-top: 2px solid #ef4444;
        }

        .stat-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px auto;
            background: #667eea;
        }

        .stat-icon.primary {
            background: #3b82f6;
        }

        .stat-icon.danger {
            background: #ef4444;
        }

        .stat-icon svg {
            width: 12px;
            height: 12px;
            color: white;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .stat-label {
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
            margin: 0 0 2px 0;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 800;
            color: #1a202c;
            margin: 0;
            line-height: 1;
        }

        .stat-value.primary {
            color: #3b82f6;
        }

        .stat-value.danger {
            color: #ef4444;
        }

        /* Títulos de secciones mejorados */
        .section-title {
            font-size: 9px;
            font-weight: 700;
            margin: 0 0 2px 0;
            display: flex;
            align-items: center;
            padding-bottom: 1px;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-title.danger {
            color: #dc2626;
            border-bottom-color: #fecaca;
        }

        .section-title.primary {
            color: #1d4ed8;
            border-bottom-color: #dbeafe;
        }

        .section-title.default {
            color: #1a202c;
            border-bottom-color: #e2e8f0;
        }

        .section-icon {
            width: 8px;
            height: 8px;
            margin-right: 2px;
            flex-shrink: 0;
        }

        /* Items de morosos mejorados */
        .moroso-item {
            background: #fef2f2;
            border-radius: 2px;
            padding: 2px;
            border-left: 1px solid #ef4444;
            margin-bottom: 1px;
        }

        .moroso-item:last-child {
            margin-bottom: 0;
        }

        .moroso-info h4 {
            font-weight: 700;
            color: #dc2626;
            margin: 0 0 1px 0;
            font-size: 7px;
        }

        .moroso-info p {
            font-size: 6px;
            color: #6b7280;
            margin: 0 0 1px 0;
        }

        .moroso-amount {
            font-weight: 800;
            color: #dc2626;
            font-size: 7px;
            margin: 1px 0 0 0;
        }

        /* Items de pagos mejorados */
        .pago-item {
            background: #f0fdf4;
            border-radius: 2px;
            padding: 2px;
            border-left: 1px solid #22c55e;
            margin-bottom: 1px;
        }

        .pago-item:last-child {
            margin-bottom: 0;
        }

        .pago-info h4 {
            font-weight: 700;
            color: #16a34a;
            margin: 0 0 1px 0;
            font-size: 7px;
        }

        .pago-info p {
            font-size: 6px;
            color: #6b7280;
            margin: 0 0 1px 0;
        }

        .pago-amount {
            text-align: right;
        }

        .pago-amount .amount {
            font-weight: 700;
            color: #16a34a;
            margin: 1px 0 0 0;
            font-size: 7px;
        }

        .pago-amount .method {
            font-size: 5px;
            color: #6b7280;
            margin: 1px 0 0 0;
            font-style: italic;
        }

        /* Estadísticas del recibo mejoradas */
        .recibo-info {
            background: #f8fafc;
            border-radius: 2px;
            padding: 3px;
            margin-bottom: 2px;
            border: 1px solid #e2e8f0;
        }

        .recibo-info h4 {
            font-size: 8px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 1px 0;
        }

        .recibo-info p {
            font-size: 6px;
            color: #64748b;
            margin: 0 0 1px 0;
        }

        .recibo-info p:last-child {
            margin-bottom: 0;
        }

        .recibo-stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1px;
        }

        .recibo-stat {
            text-align: center;
            padding: 3px;
            background: #ffffff;
            border-radius: 2px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .recibo-stat.primary {
            border-top: 1px solid #3b82f6;
        }

        .recibo-stat.success {
            border-top: 1px solid #10b981;
        }

        .recibo-stat.purple {
            border-top: 1px solid #8b5cf6;
        }

        .recibo-stat .value {
            font-size: 8px;
            font-weight: 800;
            margin: 0 0 1px 0;
        }

        .recibo-stat .label {
            font-size: 6px;
            color: #6b7280;
            margin: 0;
            font-weight: 600;
        }

        .recibo-stat.primary .value {
            color: #2563eb;
        }

        .recibo-stat.success .value {
            color: #059669;
        }

        .recibo-stat.purple .value {
            color: #7c3aed;
        }

        /* Estados vacíos mejorados */
        .empty-state {
            text-align: center;
            padding: 4px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 2px;
            color: #64748b;
            font-size: 6px;
            margin: 0;
        }

        .empty-state-icon {
            font-size: 12px;
            margin-bottom: 2px;
            opacity: 0.5;
            width: 12px;
            height: 12px;
            margin: 0 auto 2px auto;
        }

        /* Utilidades */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .text-sm { font-size: 6px; }
        .text-xs { font-size: 5px; }
        .mb-1 { margin-bottom: 1px; }
        .mb-2 { margin-bottom: 2px; }
        .mt-1 { margin-top: 1px; }
        .mt-2 { margin-top: 2px; }

        /* Espaciado mejorado */
        .space-y-4 > * + * {
            margin-top: 4px;
        }

        /* Indicadores de progreso */
        .progress-bar {
            width: 100%;
            background: #e5e7eb;
            border-radius: 2px;
            height: 4px;
            overflow: hidden;
            margin: 3px 0;
        }

        .progress-fill {
            height: 100%;
            background: #10b981;
            border-radius: 2px;
        }

        /* Elementos decorativos */
        .decorative-line {
            height: 1px;
            background: #e5e7eb;
            margin: 6px 0;
            border-radius: 1px;
        }

        /* Responsive para impresión */
        @media print {
            body {
                background: white !important;
            }
            
            .container {
                box-shadow: none !important;
            }
            
            .card:hover {
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado profesional -->
        <div class="header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h1>Reporte Dashboard Ejecutivo</h1>
                        <p>Sistema de Gestión de Comunidad Smart Community</p>
                    </div>
                </div>
                <div class="date-badge">
                    <p>{{ date('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
            <!-- Resumen ejecutivo -->
            <div class="executive-summary">
                <h2 class="summary-title">Resumen Ejecutivo</h2>
                <p style="text-align: center; font-size: 16px; color: #64748b; margin: 0;">
                    Este reporte presenta un análisis completo del estado actual de la comunidad, 
                    incluyendo métricas clave de ocupación, recaudación y gestión financiera.
                </p>
            </div>

            <!-- Estadísticas principales -->
            <div class="stats-grid">
                <!-- Total de apartamentos -->
                <div class="stat-card primary">
                    <div class="stat-icon primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="stat-label">Total de Apartamentos</p>
                    <p class="stat-value primary">{{ $totalApartamentos }}</p>
                </div>

                <!-- Saldo pendiente total -->
                <div class="stat-card danger">
                    <div class="stat-icon danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <p class="stat-label">Saldo Pendiente Total</p>
                    <p class="stat-value danger">${{ number_format($saldoPendienteTotal, 2) }}</p>
                </div>
            </div>

            <div class="decorative-line"></div>

            <!-- Secciones principales -->
            <div class="main-grid">
                <!-- Análisis de Morosidad -->
                <div class="card">
                    <h3 class="section-title danger">
                        <div class="section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        Análisis de Morosidad - Top 5 Apartamentos
                    </h3>
                    @if($apartamentosMorosos->count() > 0)
                        <div class="space-y-4">
                            @foreach($apartamentosMorosos as $apartamento)
                                <div class="moroso-item">
                                    <div class="moroso-info">
                                        <h4>Apto {{ $apartamento->numero ?? 'N/A' }}</h4>
                                        <p>{{ $apartamento->propietario ?? 'Sin propietario' }}</p>
                                    </div>
                                    <div class="moroso-amount">
                                        ${{ number_format($apartamento->saldo_pendiente, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p>¡Excelente! No hay apartamentos morosos.</p>
                        </div>
                    @endif
                </div>

                <!-- Actividad de Recaudación -->
                <div class="card">
                    <h3 class="section-title primary">
                        <div class="section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        Actividad de Recaudación - Últimos 5 Pagos
                    </h3>
                    @if($ultimosPagos->count() > 0)
                        <div class="space-y-4">
                            @foreach($ultimosPagos as $pago)
                                <div class="pago-item">
                                    <div class="pago-info">
                                        <h4>Apto {{ $pago->apartamento->numero ?? 'N/A' }}</h4>
                                        <p>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'Sin fecha' }}</p>
                                    </div>
                                    <div class="pago-amount">
                                        <p class="amount">${{ number_format($pago->monto_pagado, 2) }}</p>
                                        <p class="method">{{ ucfirst($pago->metodo_pago) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <p>No hay pagos registrados aún.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Estadísticas del Último Recibo -->
            @if($estadisticasUltimoRecibo)
                <div class="card">
                    <h3 class="section-title primary">
                        <div class="section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        Análisis Financiero - Último Recibo Generado
                    </h3>
                    <div class="recibo-info">
                        <h4>Recibo #{{ $estadisticasUltimoRecibo['recibo']->numero_recibo }}</h4>
                        <p>Período: {{ $estadisticasUltimoRecibo['recibo']->periodo }}</p>
                        <p>Valor por apartamento: ${{ number_format($estadisticasUltimoRecibo['recibo']->total_recibo, 2) }}</p>
                    </div>
                    <div class="recibo-stats-grid">
                        <div class="recibo-stat primary">
                            <p class="value">${{ number_format($estadisticasUltimoRecibo['total_recaudacion'], 2) }}</p>
                            <p class="label">Total Recaudado</p>
                        </div>
                        <div class="recibo-stat success">
                            <p class="value">{{ $estadisticasUltimoRecibo['porcentaje_recaudacion'] }}%</p>
                            <p class="label">% de Recaudación</p>
                        </div>
                        <div class="recibo-stat purple">
                            <p class="value">{{ $estadisticasUltimoRecibo['apartamentos_pagados'] }}/{{ $estadisticasUltimoRecibo['total_apartamentos'] }}</p>
                            <p class="label">Apartamentos Pagados</p>
                        </div>
                    </div>
                    
                    <!-- Indicador de progreso de recaudación -->
                    <div style="margin-top: 20px;">
                        <p style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                            Progreso de Recaudación
                        </p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $estadisticasUltimoRecibo['porcentaje_recaudacion'] }}%"></div>
                        </div>
                        <p style="font-size: 12px; color: #6b7280; margin-top: 4px; text-align: center;">
                            {{ $estadisticasUltimoRecibo['porcentaje_recaudacion'] }}% recaudado
                        </p>
                    </div>
                </div>
            @else
                <div class="card">
                    <h3 class="section-title primary">
                        <div class="section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        Análisis Financiero - Último Recibo Generado
                    </h3>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p>No hay recibos activos disponibles</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>