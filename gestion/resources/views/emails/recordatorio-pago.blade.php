<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recordatorio de Pago</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p>Estimado propietario {{ $apartamento->propietario }}</p>
        
        <p>Le recordamos que a la fecha {{ date('d/m/Y') }}</p>
        
        <p>ud presenta una deuda de {{ number_format($apartamento->saldo_pendiente, 2) }}</p>
        
        <p>Lo invitamos a formalizar el pago para solventar su deuda.</p>
        
        <br>
        <p>Atentamente, JDC Residencias Alfa</p>
    </div>
</body>
</html>
