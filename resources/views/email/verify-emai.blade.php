<!DOCTYPE html>
<html>
<head>
    <title>Verifica tu dirección de correo electrónico</title>
</head>
<body>
    <h1>Hola, {{ $user->name }}!</h1>
    <p>Haz clic en el botón a continuación para verificar tu dirección de correo electrónico.</p>
    <a href="{{ $url }}">Verificar dirección de correo</a>
    <p>Si no creaste una cuenta, no es necesario realizar ninguna otra acción.</p>
    <p>Saludos,<br>Caballos para Disfutar</p>
</body>
</html>