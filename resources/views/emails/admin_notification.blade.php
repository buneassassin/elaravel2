<!DOCTYPE html>
<html>
<head>
    <title>Nuevo Usuario Registrado</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <div style="max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <header style="background-color: #4CAF50; color: #ffffff; padding: 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">Nuevo Usuario Registrado</h1>
        </header>
        <section style="padding: 20px;">
            <p style="font-size: 16px; color: #333;">Un nuevo usuario se ha registrado en el sistema.</p>
            <div style="margin-top: 20px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 5px; background-color: #fafafa;">
                <p style="margin: 0; font-size: 16px; color: #555;"><strong>Nombre:</strong> {{ $user->name }}</p>
                <p style="margin: 5px 0 0 0; font-size: 16px; color: #555;"><strong>Email:</strong> {{ $user->email }}</p>
            </div>
        </section>
        <footer style="background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 14px; color: #888;">
            <p style="margin: 0;">&copy; 2024 Tu Empresa</p>
        </footer>
    </div>
</body>
</html>
