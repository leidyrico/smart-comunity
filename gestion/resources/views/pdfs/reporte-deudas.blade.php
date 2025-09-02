<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Deudas por Apartamento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #991b1b;
            margin: 10px 0;
        }
        
        .logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            color: #1f2937;
        }
        
        .print-filters {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .print-filters strong {
            color: #374151;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        .table th,
        .table td {
            border: 1px solid #dee2e6;
            padding: 6px 4px;
            text-align: left;
            vertical-align: top;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-red {
            color: #dc3545;
        }
        
        .text-green {
            color: #28a745;
        }
        
        .text-yellow {
            color: #ffc107;
        }
        
        .summary {
            background-color: #e3f2fd;
            padding: 15px;
            border: 1px solid #2196f3;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #2196f3;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
        
        /* Ajustes específicos para columnas */
        .col-apartamento { width: 8%; }
        .col-propietario { width: 15%; }
        .col-recibo { width: 10%; }
        .col-fecha-fact { width: 12%; }
        .col-monto-fact { width: 12%; }
        .col-monto-pag { width: 12%; }
        .col-fecha-pag { width: 12%; }
        .col-saldo { width: 12%; }
        .col-estado { width: 7%; }
    </style>
</head>
<body>
    <div class="header">
        
        <div class="company-name">Administración Residencias Alfa</div>
        <div class="report-title">Consulta de Deudas por Apartamento</div>
    </div>

    <div class="print-filters">
        @if(($filtros['numero_apartamento'] ?? '') || ($filtros['propietario'] ?? '') || ($filtros['estado_deuda'] ?? '') || ($filtros['numero_recibo'] ?? ''))
            <strong>Filtros aplicados:</strong>
            @if($filtros['numero_apartamento'] ?? '')
                Apartamento: {{ $filtros['numero_apartamento'] }} |
            @endif
            @if($filtros['propietario'] ?? '')
                Propietario: {{ $filtros['propietario'] }} |
            @endif
            @if($filtros['estado_deuda'] ?? '')
                Estado: {{ $filtros['estado_deuda'] == 'pendiente' ? 'Con saldo pendiente' : 'Pagado' }} |
            @endif
            @if($filtros['numero_recibo'] ?? '')
                Nro. Recibo: {{ $filtros['numero_recibo'] }}
            @endif
            <br>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}
        @else
            <strong>Reporte completo</strong> - Fecha de impresión: {{ now()->format('d/m/Y H:i') }}
        @endif
    </div>

    @if(count($datos) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th class="col-propietario">Propietario</th>
                    <th class="col-apartamento">Apartamento</th>
                    <th class="col-recibo">Nro Recibo</th>
                    <th class="col-fecha-fact">Fecha Facturación</th>
                    <th class="col-monto-fact">Monto Facturado</th>
                    <th class="col-monto-pag">Monto Pagado</th>
                    <th class="col-fecha-pag">Fecha Pago</th>
                    <th class="col-saldo">Saldo Actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos as $item)
                    <tr>
                        <td>{{ $item['propietario'] }}</td>
                        <td class="text-center">{{ $item['numero_apartamento'] }}</td>
                        <td class="text-center">{{ $item['numero_recibo'] }}</td>
                        <td class="text-center">{{ $item['periodo'] }}</td>
                        <td class="text-right">${{ number_format($item['total_recibo'], 2, ',', '.') }}</td>
                        <td class="text-right">
                            @if($item['total_pagado'] > 0)
                                <span class="text-green">${{ number_format($item['total_pagado'], 2, ',', '.') }}</span>
                            @else
                                <span style="color: #999;">$0.00</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item['fecha_pago'])
                                {{ \Carbon\Carbon::parse($item['fecha_pago'])->format('d/m/Y') }}
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if($item['saldo_actual'] > 0)
                                <span class="text-red">${{ number_format($item['saldo_actual'], 2, ',', '.') }}</span>
                            @else
                                <span class="text-green">$0.00</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Resumen estadístico -->
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Registros</div>
                    <div class="summary-value">{{ count($datos) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Con Saldo Pendiente</div>
                    <div class="summary-value">{{ collect($datos)->where('saldo_actual', '>', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Pagados</div>
                    <div class="summary-value">{{ collect($datos)->where('saldo_actual', '<=', 0)->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Saldo Total Pendiente</div>
                    <div class="summary-value text-red">${{ number_format(collect($datos)->sum('saldo_actual'), 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 40px; color: #6c757d;">
            <p><strong>No se encontraron registros con los filtros aplicados.</strong></p>
        </div>
    @endif

    <div class="footer">
        <p>Este reporte fue generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Smart Comunity - Sistema de Gestión de Condominios</p>
    </div>
</body>
</html>