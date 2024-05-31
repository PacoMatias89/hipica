<!DOCTYPE html>
<html>
<head>
    <title>Reserva - Caballos para disfrutar</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #1e3a8a;
            font-size: 2em;
            margin-bottom: 20px;
        }
        .details {
            font-size: 1.1em;
            line-height: 1.6;
        }
        .details p {
            margin: 10px 0;
        }
        .details p strong {
            display: inline-block;
            width: 150px;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Caballos para disfrutar</h1>
        <div class="details">
            <p><strong>Fecha:</strong> {{ $booking->date }}</p>
            <p><strong>Hora:</strong> {{ $booking->time }}</p>
            <p><strong>Comentarios:</strong> {{ $booking->comments }}</p>
            <p><strong>Caballo:</strong> {{ $booking->horse->name }}</p>
            <p><strong>Precio del Caballo:</strong> {{ $booking->horse->price }}</p>
        </div>
    </div>
</body>
<script>
        document.getElementById('generatePdfButton').addEventListener('click', function() {
            // Utiliza html2pdf para convertir el contenido actual a PDF
            const element = document.body;
            html2pdf().from(element).save();
        });
    </script>
</html>

