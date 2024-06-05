<x-app-layout>
    <div class="py-12" style="background: cornsilk;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" style="background: burlywood;">
                <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg  p-6">
                    <div class="flex justify-center items-center mb-4">
                        <h1 class="text-2xl font-bold">Mis reservas</h1>
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success">
                            <script>
                                alert("{{ session('success') }}");
                            </script>
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700" style="background: blanchedalmond;">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caballo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($bookings as $booking)
                                @php
                                    $isPast = \Carbon\Carbon::parse($booking->date . ' ' . $booking->time)->isPast();
                                @endphp
                                <tr class="{{ $isPast ? 'bg-gray-800 text-gray-500' : '' }}">
                                    <td class="px-6 py-4 text-center">{{ $booking->date }}</td>
                                    <td class="px-6 py-4 text-center">{{ $booking->time }}</td>
                                    <td class="px-6 py-4 text-center">{{ $booking->horse->name }}</td>
                                    <td class="px-6 py-4 text-center">{{ $booking->comments }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center">
                                            @unless ($isPast)
                                                <a href="{{ route('bookings.edit', $booking->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    <img src="{{ asset('icons/editar.png') }}" alt="editar" class="w-6 h-6">
                                                </a>
                                                <div class="w-4"></div> <!-- Espacio entre iconos -->
                                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('¿De verdad quieres eliminar la reserva?')" class="text-red-600 hover:text-red-900">
                                                        <img src="{{ asset('icons/eliminar.png') }}" alt="eliminar" class="w-6 h-6">
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center">No bookings found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
