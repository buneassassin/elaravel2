<!DOCTYPE html>
<html lang="en">

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
        <p>¡Bienvenido! Por favor, haz clic en el enlace a continuación para activar tu cuenta:</p>
        <a href="{{ $activationLink }}">Activar Cuenta</a>
        <p>Este enlace es válido por 5 minutos.</p>
        <div class="footer">
            Si no solicitaste este correo, puedes ignorarlo.
        </div>
    </div>
</body>

</html>
