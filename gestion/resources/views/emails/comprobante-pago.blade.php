<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de pago recibido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('logo.png') }}" alt="Residencias Alfa Logo" class="logo">
        <h1>Administración Residencias Alfa</h1>
        <h2>Confirmación de Pago Recibido</h2>
    </div>

    <div class="content">
        <p>Estimado(a) <strong>{{ $pago->apartamento->propietario }}</strong>,</p>
        
        <p>Le informamos que hemos recibido su pago por <span class="amount">${{ number_format($pago->monto_pagado, 2, ',', '.') }}</span>, correspondiente al recibo N.º <strong>{{ $pago->reciboGastoComun->numero_recibo }}</strong>, con fecha <strong>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</strong> y comprobante N.º <strong>{{ $pago->numero_comprobante ?? 'N/A' }}</strong>.</p>
        
        <div class="details">
            <h3>Detalles del Pago:</h3>
            <ul>
                <li><strong>Apartamento:</strong> {{ $pago->apartamento->numero }}</li>
                <li><strong>Período:</strong> {{ $pago->reciboGastoComun->periodo }}</li>
                <li><strong>Método de pago:</strong> {{ ucfirst(str_replace('_', ' ', $pago->metodo_pago)) }}</li>
                <li><strong>Estado:</strong> {{ ucfirst($pago->estado) }}</li>
                @if($pago->observaciones)
                <li><strong>Observaciones:</strong> {{ $pago->observaciones }}</li>
                @endif
            </ul>
        </div>
        
        <p>Agradecemos su puntualidad y confianza.</p>
        
        <p>Atentamente,<br>
        <strong>Administración Residencias Alfa</strong></p>
    </div>

    <div class="footer">
        <p><small>Este es un correo automático, por favor no responda a este mensaje.</small></p>
    </div>
</body>
</html>