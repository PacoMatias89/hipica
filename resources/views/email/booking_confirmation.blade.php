<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="mt-3 mb-3 text-lg font-semibold">Confirmación de Reserva</h1>

                <p>Se ha realizado una reserva con éxito. A continuación se muestran los detalles:</p>
                
                <p><strong>Fecha:</strong> {{ $booking->date }}</p>
                <p><strong>Hora:</strong> {{ $booking->time }}</p>
                <p><strong>Comentarios:</strong> {{ $booking->comments }}</p>
                <p><strong>Caballo:</strong> {{ $booking->horse->name }}</p>

                <p>¡Gracias por utilizar nuestro servicio!</p>
            </div>
        </div>
    </div>
</x-app-layout>