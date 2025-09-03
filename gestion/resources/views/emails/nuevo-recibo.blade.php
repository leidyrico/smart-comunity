<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emisión de nuevo recibo de gasto común</title>
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
            color: #dc3545;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Administración Residencias Alfa</h1>
        <h2>Emisión de nuevo recibo de gasto común</h2>
    </div>

    <div class="content">
        <p>Estimado(a) <strong>{{ $propietario ?? 'propietario(a)/residente' }}</strong>,</p>
        
        <p>Por medio de la presente le informamos que se ha generado un nuevo recibo de gasto común, correspondiente al periodo <strong>{{ $recibo->periodo }}</strong>, con la siguiente información:</p>
        
        <div class="details">
            <h3>Información del Recibo:</h3>
            <ul>
                <li><strong>Número de recibo:</strong> {{ $recibo->numero_recibo }}</li>
                <li><strong>Período:</strong> {{ $recibo->periodo }}</li>
                <li><strong>Fecha de emisión:</strong> {{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') }}</li>
                <li><strong>Fecha de vencimiento:</strong> {{ \Carbon\Carbon::parse($recibo->fecha_vencimiento)->format('d/m/Y') }}</li>
                <li><strong>Monto total:</strong> <span class="amount">${{ number_format($recibo->total_recibo, 2, ',', '.') }}</span></li>
            </ul>
        </div>
        
        <div class="highlight">
            <p><strong>Le recordamos la importancia de realizar el pago dentro del plazo establecido para mantener al día las obligaciones comunitarias.</strong></p>
        </div>
        
        @if($recibo->observaciones)
        <div class="details">
            <h3>Observaciones:</h3>
            <p>{{ $recibo->observaciones }}</p>
        </div>
        @endif
        
        <p>Agradecemos de antemano su puntualidad y compromiso.</p>
        
        <p>Atentamente,<br>
        <strong>Residencias Alfa</strong></p>
    </div>

    <div class="footer">
        <p><small>Este es un correo automático, por favor no responda a este mensaje.</small></p>
        @if($recibo->archivo_adjunto)
        <p><small>Se adjunta el archivo correspondiente al recibo.</small></p>
        @endif
    </div>
</body>
</html>