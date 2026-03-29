<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
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
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            background-color: #f1f1f1;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .link {
            word-break: break-all;
            background-color: #eee;
            padding: 10px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema Dactilar INCES</h1>
        <p>Recuperación de Contraseña</p>
    </div>
    
    <div class="content">
        <h2>Hola, {{ $empleado->nombre }} {{ $empleado->apellido }}</h2>
        
        <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
        
        <p>Para continuar con el proceso de recuperación, haz clic en el siguiente botón:</p>
        
        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Restablecer Contraseña</a>
        </p>
        
        <p>O copia y pega el siguiente enlace en tu navegador:</p>
        <div class="link">{{ $resetUrl }}</div>
        
        <p><strong>Nota:</strong> Este enlace expira en 1 hora por motivos de seguridad.</p>
        
        <p>Si no solicitaste este cambio, puedes ignorar este correo electrónico.</p>
    </div>
    
    <div class="footer">
        <p>Este es un correo automático del Sistema Dactilar INCES.</p>
        <p>Por favor, no respondas directamente a este mensaje.</p>
    </div>
</body>
</html>
