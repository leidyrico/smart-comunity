<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Deudas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #991b1b;
            margin: 10px 0;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #1f2937;
        }
        .filters-info {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filters-info strong {
            color: #374151;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .amount {
            text-align: right;
            font-weight: bold;
        }
        .status-pendiente {
            color: #dc2626;
            font-weight: bold;
        }
        .status-pagado {
            color: #059669;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .summary {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 1px solid #2563eb;
            padding-top: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">

        <div class="company-name">Administración Residencias Alfa</div>
        <div class="report-title">Reporte de Deudas por Apartamento</div>
    </div>

    <div class="greeting" style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2563eb;">
        <p style="margin: 0 0 15px 0; font-size: 16px;"><strong>Estimado Propietario,</strong></p>
        <p style="margin: 0; font-size: 14px; line-height: 1.5;">Adjunto al correo se encuentra su estado de cuenta.</p>
    </div>

    @if($apartamento)
        <div style="background-color: #fef3c7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong>Apartamento:</strong> {{ $apartamento->numero }}<br>
            <strong>Propietario:</strong> {{ $apartamento->propietario }}<br>
            @if($apartamento->email)
                <strong>Email:</strong> {{ $apartamento->email }}<br>
            @endif
        </div>
    @endif

    @if(count($datos) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Apartamento</th>
                    <th>Propietario</th>
                    <th>Nro. Recibo</th>
                    <th>Período</th>
                    <th>Total Recibo</th>
                    <th>Total Pagado</th>
                    <th>Saldo Actual</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalRecibos = 0;
                    $totalPagado = 0;
                    $totalSaldo = 0;
                @endphp
                @foreach($datos as $dato)
                    @php
                        $totalRecibos += $dato['total_recibo'];
                        $totalPagado += $dato['total_pagado'];
                        $totalSaldo += $dato['saldo_actual'];
                    @endphp
                    <tr>
                        <td>{{ $dato['numero_apartamento'] }}</td>
                        <td>{{ $dato['propietario'] }}</td>
                        <td>{{ $dato['numero_recibo'] }}</td>
                        <td>{{ $dato['periodo'] }}</td>
                        <td class="amount">${{ number_format($dato['total_recibo'], 2, ',', '.') }}</td>
                        <td class="amount">${{ number_format($dato['total_pagado'], 2, ',', '.') }}</td>
                        <td class="amount">${{ number_format($dato['saldo_actual'], 2, ',', '.') }}</td>
                        <td class="{{ $dato['saldo_actual'] > 0 ? 'status-pendiente' : 'status-pagado' }}">
                            {{ $dato['saldo_actual'] > 0 ? 'Pendiente' : 'Pagado' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-item">
                <span>Total de Recibos:</span>
                <span class="amount">${{ number_format($totalRecibos, 2, ',', '.') }}</span>
            </div>
            <div class="summary-item">
                <span>Total Pagado:</span>
                <span class="amount">${{ number_format($totalPagado, 2, ',', '.') }}</span>
            </div>
            <div class="summary-item summary-total">
                <span>Saldo Pendiente Total:</span>
                <span class="amount">${{ number_format($totalSaldo, 2, ',', '.') }}</span>
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 40px; background-color: #f9fafb; border-radius: 8px;">
            <p style="font-size: 18px; color: #6b7280;">No se encontraron registros con los filtros aplicados.</p>
        </div>
    @endif

    <div class="closing" style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin: 30px 0 20px 0; border-left: 4px solid #2563eb;">
        <p style="margin: 0 0 10px 0; font-size: 14px;">Saludos,</p>
        <p style="margin: 0; font-size: 14px; font-weight: bold;">Administración Residencias Alfa</p>
    </div>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el Sistema de Gestión de Residencias Alfa.</p>
        <p>Para consultas o aclaraciones, contacte a la administración.</p>
    </div>
</body>
</html>