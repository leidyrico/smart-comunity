<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1F2937;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #6B7280;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .card h3 {
            color: #1F2937;
            margin: 0 0 15px 0;
            font-size: 18px;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #F9FAFB;
            border-radius: 6px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #6B7280;
            text-transform: uppercase;
        }
        .text-blue { color: #3B82F6; }
        .text-red { color: #EF4444; }
        .text-green { color: #10B981; }
        .text-purple { color: #8B5CF6; }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th,
        .table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
            font-size: 12px;
        }
        .table th {
            background: #F9FAFB;
            font-weight: bold;
            color: #374151;
        }
        .urgency-high {
            background-color: #FEF2F2;
            color: #DC2626;
        }
        .urgency-medium {
            background-color: #FFFBEB;
            color: #D97706;
        }
        .urgency-low {
            background-color: #F0FDF4;
            color: #059669;
        }
        .recibo-info {
            background: #EFF6FF;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .recibo-info p {
            margin: 2px 0;
            font-size: 12px;
            color: #1E40AF;
        }
        .stats-recibo {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Dashboard del Sistema</h1>
        <p>Reporte generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Mensaje de bienvenida -->
    <div class="card">
        <h3>Sistema de Gestión de Comunidad</h3>
        <p>Resumen general del estado actual de la comunidad y sus finanzas.</p>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="card">
        <h3>Estadísticas Generales</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value text-blue">{{ $totalApartamentos }}</div>
                <div class="stat-label">Total Apartamentos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value text-red">${{ number_format($saldoPendienteTotal, 2) }}</div>
                <div class="stat-label">Saldo Pendiente Total</div>
            </div>
        </div>
    </div>

    <!-- Top 5 apartamentos morosos -->
    <div class="card">
        <h3>Top 5 Apartamentos con Mayor Deuda</h3>
        @if($apartamentosMorosos->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Apartamento</th>
                        <th>Propietario</th>
                        <th>Saldo Pendiente</th>
                        <th>Urgencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apartamentosMorosos as $apartamento)
                        <tr>
                            <td>{{ $apartamento->numero }}</td>
                            <td>{{ $apartamento->propietario }}</td>
                            <td>${{ number_format($apartamento->saldo_pendiente, 2) }}</td>
                            <td>
                                @if($apartamento->saldo_pendiente > 500000)
                                    <span class="urgency-high">Alta</span>
                                @elseif($apartamento->saldo_pendiente > 200000)
                                    <span class="urgency-medium">Media</span>
                                @else
                                    <span class="urgency-low">Baja</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #6B7280; padding: 20px;">No hay apartamentos con deudas pendientes</p>
        @endif
    </div>

    <!-- Últimos 5 pagos -->
    <div class="card">
        <h3>Últimos 5 Pagos Confirmados</h3>
        @if($ultimosPagos->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Apartamento</th>
                        <th>Monto</th>
                        <th>Método</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosPagos as $pago)
                        <tr>
                            <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $pago->apartamento->numero ?? 'N/A' }}</td>
                            <td>${{ number_format($pago->monto_pagado, 2) }}</td>
                            <td>{{ ucfirst($pago->metodo_pago) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #6B7280; padding: 20px;">No hay pagos registrados</p>
        @endif
    </div>

    <!-- Estadísticas del Último Recibo -->
    @if($estadisticasUltimoRecibo)
        <div class="card">
            <h3>Estadísticas del Último Recibo</h3>
            <div class="recibo-info">
                <p><strong>Recibo:</strong> {{ $estadisticasUltimoRecibo['recibo']->numero_recibo }}</p>
                <p><strong>Período:</strong> {{ $estadisticasUltimoRecibo['recibo']->periodo }}</p>
                <p><strong>Valor por apartamento:</strong> ${{ number_format($estadisticasUltimoRecibo['recibo']->total_recibo, 2) }}</p>
            </div>
            <div class="stats-recibo">
                <div class="stat-item">
                    <div class="stat-value text-blue">${{ number_format($estadisticasUltimoRecibo['total_recaudacion'], 2) }}</div>
                    <div class="stat-label">Total Recaudado</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-green">{{ $estadisticasUltimoRecibo['porcentaje_recaudacion'] }}%</div>
                    <div class="stat-label">% de Recaudación</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-purple">{{ $estadisticasUltimoRecibo['apartamentos_pagados'] }}/{{ $estadisticasUltimoRecibo['total_apartamentos'] }}</div>
                    <div class="stat-label">Apartamentos Pagados</div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <h3>Estadísticas del Último Recibo</h3>
            <p style="text-align: center; color: #6B7280; padding: 20px;">No hay recibos activos disponibles</p>
        </div>
    @endif
</body>
</html>