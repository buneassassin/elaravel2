<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de cuenta</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #4CAF50;
            font-size: 24px;
        }
        p {
            font-size: 16px;
            color: #555555;
        }
        a {
            display: inline-block;
            padding: 12px 20px;
            color: #ffffff;
            background-color: #4CAF50;
            text-decoration: none;
            font-size: 16px;
            border-radius: 4px;
            margin-top: 20px;
        }
        a:hover {
            background-color: #45a049;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888888;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>Activación de cuenta</h1>
        <p>¡Bienvenido! A continuación se muestra tu código de activación:</p>
        
        <h2>{{ $activationCode }}</h2>
        <p>Ingresa este código en la siguiente interfaz para activar tu cuenta:</p>
        <a href="{{ $activationLink }}">Ir a Activación</a>
        <p>No hay límite de tiempo para utilizar este código, pero solo es válido mientras no se haya activado la cuenta.</p>
        <div class="footer">
            Si no solicitaste este correo, puedes ignorarlo.
        </div>
    </div>
</body>
</html>
