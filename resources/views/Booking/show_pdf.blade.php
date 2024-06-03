<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Reserva de Caballos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.7/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .invoice-title {
            font-size: 2em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1em;
        }
        .invoice-details {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details th, .invoice-details td {
            border: 1px solid #000;
            padding: 0.5em;
        }
        .invoice-details th {
            background-color: #f8f8f8;
            text-align: left;
        }
        .total-price {
            color: #1a202c;
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 1em;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-8 bg-white rounded-lg shadow-lg mt-16">
        <h1 class="invoice-title">Factura de Reserva - Caballos para disfrutar</h1>
        <table class="invoice-details">
            <tr>
                <th>Fecha</th>
                <td>{{ $booking->date }}</td>
            </tr>
            <tr>
                <th>Hora</th>
                <td>{{ $booking->time }}</td>
            </tr>
            <tr>
                <th>Caballo</th>
                <td>{{ $booking->horse->name }}</td>
            </tr>
            <tr>
                <th>Precio del Caballo</th>
                <td>{{ $booking->horse->price }}</td>
            </tr>
            <tr>
                <th>Comentarios</th>
                <td>{{ $booking->comments }}</td>
            </tr>
        </table>
        <p class="total-price">Precio Total: {{ $booking->horse -> price }} €</p>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('generatePdfButton').addEventListener('click', function() {
            // Utiliza html2pdf para convertir el contenido actual a PDF
            const element = document.body;
            html2pdf().from(element).save();
        });
    </script>
</body>
</html>