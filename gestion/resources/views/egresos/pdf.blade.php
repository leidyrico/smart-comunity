<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Egreso - {{ $egreso->nro_factura }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #7f8c8d;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 30%;
            padding: 8px 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            font-weight: bold;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .amount-section {
            background-color: #e8f4fd;
            border: 2px solid #3498db;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .amount-section h3 {
            margin: 0 0 10px 0;
            color: #2980b9;
        }
        
        .amount-usd {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }
        
        .amount-bs {
            font-size: 16px;
            color: #7f8c8d;
        }
        
        .description-section {
            margin: 20px 0;
        }
        
        .description-box {
            border: 1px solid #dee2e6;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 3px;
            min-height: 60px;
        }
        
        .signatures-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signatures-container {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-box {
            display: table-cell;
            width: 45%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: bold;
        }
        
        .signature-title {
            margin-bottom: 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .signature-subtitle {
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .date-generated {
            text-align: right;
            font-size: 10px;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="date-generated">
        Generado el: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <div class="header">
        <h1>COMPROBANTE DE EGRESO</h1>
        <h2>Smart Community - Sistema de Gestión</h2>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Número de Factura:</div>
                <div class="info-value">{{ $egreso->nro_factura }}</div>
            </div>
            
            @if($egreso->comprobante)
            <div class="info-row">
                <div class="info-label">Número de Comprobante:</div>
                <div class="info-value">{{ $egreso->comprobante }}</div>
            </div>
            @endif
            
            <div class="info-row">
                <div class="info-label">Fecha:</div>
                <div class="info-value">{{ $egreso->fecha->format('d/m/Y') }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Proveedor:</div>
                <div class="info-value">
                    @if($egreso->proveedor)
                        {{ $egreso->proveedor->nombre }}
                        @if($egreso->proveedor->rif)
                            <br><small>RIF: {{ $egreso->proveedor->rif }}</small>
                        @endif
                        @if($egreso->proveedor->telefono)
                            <br><small>Teléfono: {{ $egreso->proveedor->telefono }}</small>
                        @endif
                        @if($egreso->proveedor->email)
                            <br><small>Email: {{ $egreso->proveedor->email }}</small>
                        @endif
                    @else
                        No especificado
                    @endif
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Fecha de Registro:</div>
                <div class="info-value">{{ $egreso->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
    </div>

    <div class="amount-section">
        <h3>MONTO DEL EGRESO</h3>
        <div class="amount-usd">
            ${{ number_format($egreso->monto, 2) }} USD
        </div>
        @if($egreso->monto_en_bs)
        <div class="amount-bs">
            Bs {{ number_format($egreso->monto_en_bs, 2) }}
        </div>
        @endif
    </div>

    @if($egreso->descripcion)
    <div class="description-section">
        <h3 style="margin-bottom: 10px; color: #2c3e50;">Descripción:</h3>
        <div class="description-box">
            {{ $egreso->descripcion }}
        </div>
    </div>
    @endif

    <div class="signatures-section">
        <h3 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">FIRMAS DE AUTORIZACIÓN</h3>
        
        <div class="signatures-container">
            <div class="signature-box">
                <div class="signature-title">ADMINISTRADOR</div>
                <div class="signature-line">
                    Firma del Administrador
                </div>
                <div class="signature-subtitle">
                    Nombre: _________________________<br>
                    Fecha: ___________________________
                </div>
            </div>
            
            <div style="display: table-cell; width: 10%; text-align: center;">
                <!-- Espacio entre firmas -->
            </div>
            
            <div class="signature-box">
                <div class="signature-title">PROVEEDOR</div>
                <div class="signature-line">
                    Firma del Proveedor
                </div>
                <div class="signature-subtitle">
                    Nombre: _________________________<br>
                    Fecha: ___________________________
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento es un comprobante oficial del egreso registrado en el sistema Smart Community.</p>
        <p>Para verificar la autenticidad de este documento, contacte a la administración.</p>
    </div>
</body>
</html>