<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $recibo->numero_recibo }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2563eb;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 18px;
            color: #666;
        }
        
        .recibo-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .recibo-info div {
            flex: 1;
        }
        
        .recibo-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2563eb;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .conceptos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .conceptos-table th,
        .conceptos-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .conceptos-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2563eb;
        }
        
        .conceptos-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #f0f9ff;
            font-weight: bold;
            font-size: 14px;
        }
        
        .total-row td {
            border-top: 2px solid #2563eb;
        }
        
        .observaciones {
            margin-bottom: 30px;
        }
        
        .observaciones h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2563eb;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .observaciones-content {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
        }
        
        .pagos-section {
            margin-bottom: 30px;
        }
        
        .pagos-section h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #2563eb;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .pagos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .pagos-table th,
        .pagos-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .pagos-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .estado-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .estado-activo { background-color: #dcfce7; color: #166534; }
        .estado-vencido { background-color: #fecaca; color: #991b1b; }
        .estado-anulado { background-color: #f3f4f6; color: #374151; }
        .estado-confirmado { background-color: #dcfce7; color: #166534; }
        .estado-pendiente { background-color: #fef3c7; color: #92400e; }
        .estado-rechazado { background-color: #fecaca; color: #991b1b; }
        
        .resumen-pagos {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
        }
        
        .resumen-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .resumen-row:last-child {
            margin-bottom: 0;
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #2563eb;
            padding-top: 5px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir</button>
    
    <div class="header">
        <h1>SMART COMUNITY</h1>
        <h2>Recibo de Gasto Común</h2>
    </div>
    
    <div class="recibo-info">
        <div style="margin-right: 30px;">
            <h3>Información del Recibo</h3>
            <div class="info-row">
                <span class="info-label">Número:</span>
                <span class="info-value">{{ $recibo->numero_recibo }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Período:</span>
                <span class="info-value">{{ $recibo->periodo }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    <span class="estado-badge estado-{{ $recibo->estado }}">
                        {{ ucfirst($recibo->estado) }}
                    </span>
                </span>
            </div>
        </div>
        
        <div>
            <h3>Fechas</h3>
            <div class="info-row">
                <span class="info-label">Emisión:</span>
                <span class="info-value">{{ $recibo->fecha_emision->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vencimiento:</span>
                <span class="info-value">{{ $recibo->fecha_vencimiento->format('d/m/Y') }}</span>
            </div>
            @if($recibo->estaVencido())
            <div class="info-row">
                <span class="info-label" style="color: #dc2626;">⚠️ VENCIDO</span>
                <span class="info-value"></span>
            </div>
            @endif
        </div>
    </div>
    
    <table class="conceptos-table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="amount">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Administración</td>
                <td class="amount">${{ number_format($recibo->valor_administracion, 0, ',', '.') }}</td>
            </tr>
            @if($recibo->valor_aseo > 0)
            <tr>
                <td>Aseo</td>
                <td class="amount">${{ number_format($recibo->valor_aseo, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($recibo->valor_vigilancia > 0)
            <tr>
                <td>Vigilancia</td>
                <td class="amount">${{ number_format($recibo->valor_vigilancia, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($recibo->valor_mantenimiento > 0)
            <tr>
                <td>Mantenimiento</td>
                <td class="amount">${{ number_format($recibo->valor_mantenimiento, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($recibo->otros_conceptos > 0)
            <tr>
                <td>Otros Conceptos</td>
                <td class="amount">${{ number_format($recibo->otros_conceptos, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td><strong>TOTAL A PAGAR</strong></td>
                <td class="amount"><strong>${{ number_format($recibo->total_recibo, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    @if($recibo->observaciones)
    <div class="observaciones">
        <h3>Observaciones</h3>
        <div class="observaciones-content">
            {{ $recibo->observaciones }}
        </div>
    </div>
    @endif
    
    @if($recibo->pagos->count() > 0)
    <div class="pagos-section">
        <h3>Historial de Pagos</h3>
        <table class="pagos-table">
            <thead>
                <tr>
                    <th>Apartamento</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Comprobante</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recibo->pagos as $pago)
                <tr>
                    <td>{{ $pago->apartamento->numero ?? 'N/A' }}</td>
                    <td class="amount">${{ number_format($pago->monto_pagado, 0, ',', '.') }}</td>
                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</td>
                    <td>{{ $pago->numero_comprobante ?? 'N/A' }}</td>
                    <td>
                        <span class="estado-badge estado-{{ $pago->estado }}">
                            {{ ucfirst(str_replace('_', ' ', $pago->estado)) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="resumen-pagos">
            <div class="resumen-row">
                <span>Total del Recibo:</span>
                <span>${{ number_format($recibo->total_recibo, 0, ',', '.') }}</span>
            </div>
            <div class="resumen-row">
                <span>Total Pagado:</span>
                <span>${{ number_format($recibo->total_pagado, 0, ',', '.') }}</span>
            </div>
            <div class="resumen-row">
                <span>Saldo Pendiente:</span>
                <span style="color: {{ $recibo->saldo_pendiente > 0 ? '#dc2626' : '#059669' }}">
                    ${{ number_format($recibo->saldo_pendiente, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
    @endif
    
    <div class="footer">
        <p>Este recibo fue generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Smart Comunity - Sistema de Gestión de Condominios</p>
    </div>
    
    <script>
        // Auto-focus para impresión
        window.addEventListener('load', function() {
            // Si viene de un enlace de impresión directa, abrir diálogo de impresión
            if (window.location.search.includes('print=true')) {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>
</html>