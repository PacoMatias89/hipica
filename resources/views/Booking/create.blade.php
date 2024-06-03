<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="mt-3 mb-3 text-lg font-semibold">Crear Reserva</h1>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    <script>
                        alert("Se ha añadido una reserva. Verifique su correo electrónico para los detalles de la reserva.");
                    </script>
                @endif
                
                <form id="bookingForm" method="POST" action="{{ route('bookings.store') }}">
                    @csrf
                    <div class="mb-6">
                        <label for="date" class="block text-sm font-medium text-gray-700">Fecha</label>
                        <input type="date" name="date" id="bookingDate" onchange="checkWeekend(this)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required min="{{ now()->toDateString() }}" max="{{ now()->addDays(30)->toDateString() }}">
                    </div>

                    <div class="mb-6">
                        <label for="time" class="block text-sm font-medium text-gray-700">Hora</label>
                        <select name="time" id="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Seleccione una hora</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="horse_id" class="block text-sm font-medium text-gray-700">Caballo</label>
                        <select name="horse_id" id="horse_id" onchange="updatePrice(this);" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Seleccione un caballo</option>
                            @foreach($horses as $horse)
                                @if(!$horse->sick)
                                    <option value="{{ $horse->id }}" data-price="{{ $horse->price }}">{{ $horse->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="horse_price" class="block text-sm font-medium text-gray-700">Precio del Caballo</label>
                        <input type="text" id="horse_price" name="horse_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50" readonly>
                    </div>

                    <div class="mb-6">
                        <label for="comments" class="block text-sm font-medium text-gray-700">Comentarios</label>
                        <textarea name="comments" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3" placeholder="Comentarios sobre la reserva (opcional)"></textarea>
                    </div>

                    <div class="mb-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-900">
                            Hacer Reserva
                        </button>

                        <a href="{{ route('bookings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-900">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function updatePrice(selectElement) {
        var horseId = selectElement.value;
        var horsePrice = document.querySelector('option[value="' + horseId + '"]').getAttribute('data-price');
        document.getElementById('horse_price').value = horsePrice;
    }

    function checkWeekend(inputElement) {
        var date = new Date(inputElement.value);
        var day = date.getDay();

        if (day !== 6 && day !== 0) {
            alert('Las reservas solo están permitidas los sábados y domingos.');
            inputElement.value = '';
        }
    }
</script>
