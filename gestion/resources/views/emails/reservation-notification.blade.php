<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva</title>
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
            color: #28a745;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .highlight {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="color: #495057; margin: 0;">Residencias Alfa</h1>
        <h2 style="color: #6c757d; margin: 10px 0 0 0;">Confirmación de Reserva</h2>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $apartamento->propietario }}</strong>,</p>
        
        <p>Nos complace confirmar que su reserva ha sido procesada exitosamente. A continuación, encontrará los detalles de su reserva:</p>

        <div class="highlight">
            <h3 style="margin-top: 0; color: #28a745;">✓ Reserva Confirmada</h3>
            <p style="margin-bottom: 0;">Su espacio ha sido reservado correctamente.</p>
        </div>

        <div class="details">
            <h3 style="margin-top: 0; color: #495057;">Detalles de la Reserva</h3>
            
            <div class="info-row">
                <span class="info-label">Apartamento:</span>
                <span class="info-value">{{ $apartamento->numero }} - Torre {{ $apartamento->torre }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Espacio Reservado:</span>
                <span class="info-value">{{ $space->nombre }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Descripción:</span>
                <span class="info-value">{{ $space->descripcion }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Ubicación:</span>
                <span class="info-value">{{ $space->ubicacion }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Fecha de Reserva:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($reservation->fecha_reserva)->format('d/m/Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Monto:</span>
                <span class="info-value amount">${{ number_format($reservation->monto, 0, ',', '.') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value" style="color: #28a745; font-weight: bold;">{{ ucfirst($reservation->estado) }}</span>
            </div>
            
            @if($reservation->observaciones)
            <div class="info-row">
                <span class="info-label">Observaciones:</span>
                <span class="info-value">{{ $reservation->observaciones }}</span>
            </div>
            @endif
        </div>

        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #856404;">Información Importante</h4>
            <ul style="margin-bottom: 0; color: #856404;">
                <li>Por favor, conserve este correo como comprobante de su reserva.</li>
                <li>Recuerde cumplir con las normas de uso del espacio reservado.</li>
                <li>En caso de necesitar cancelar o modificar su reserva, contacte a la administración.</li>
            </ul>
        </div>

        <p>Si tiene alguna pregunta o necesita asistencia adicional, no dude en contactarnos.</p>
        
        <p>Gracias por utilizar nuestros servicios.</p>
    </div>

    <div class="footer">
        <p style="margin: 0; color: #6c757d;">
            <strong>Residencias Alfa</strong><br>
            Sistema de Gestión de Comunidad<br>
            Este es un correo automático, por favor no responda a esta dirección.
        </p>
    </div>
</body>
</html>