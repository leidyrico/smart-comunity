<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva comunicación</title>
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
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .highlight {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .document-type {
            font-weight: bold;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Administración Residencias Alfa</h1>
        <h2>Nueva comunicación</h2>
    </div>

    <div class="content">
        <p>Estimado(a) <strong>{{ $propietario ?? 'propietario(a)/residente' }}</strong>,</p>
        
        <p>Por medio de la presente le informamos que se ha publicado una nueva comunicación para su conocimiento:</p>
        
        <div class="details">
            <h3>Información del Documento:</h3>
            <ul>
                <li><strong>Número de documento:</strong> {{ $acta->nro_doc }}</li>
                <li><strong>Nombre:</strong> {{ $acta->nombre_doc }}</li>
                <li><strong>Tipo de documento:</strong> <span class="document-type">{{ $acta->tipo_documento }}</span></li>
                <li><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</li>
            </ul>
        </div>
        
        @if($acta->descripcion)
        <div class="details">
            <h3>Descripción:</h3>
            <p>{{ $acta->descripcion }}</p>
        </div>
        @endif
        
        <div class="highlight">
            <p><strong>Le invitamos a revisar el documento adjunto para conocer los detalles completos de esta comunicación.</strong></p>
        </div>
        
        <p>Agradecemos su atención y colaboración.</p>
        
        <p>Atentamente,<br>
        <strong>Residencias Alfa</strong></p>
    </div>

    <div class="footer">
        <p><small>Este es un correo automático, por favor no responda a este mensaje.</small></p>
        @if($acta->archivo_contenido)
        <p><small>Se adjunta el documento correspondiente a esta comunicación.</small></p>
        @endif
    </div>
</body>
</html>