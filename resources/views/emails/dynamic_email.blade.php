<!-- resources/views/emails/dynamic_email.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px 0;
            background-color: #007bff;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        h1 {
            margin: 0;
        }
        .content {
            padding: 20px;
        }
        p {
            line-height: 1.6;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido a Example App!</h1>
        </div>
        <div class="content">
            <h2>Hola, {{ $name }}!</h2>
            <p>¡Estamos felices de tenerte aquí!</p>
            <p>Tu correo electrónico es: <strong>{{ $email }}</strong></p>
            <p>Explora nuestras funcionalidades y disfruta de la experiencia.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Example App. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
