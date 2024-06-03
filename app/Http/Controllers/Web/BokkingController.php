<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bokking;
use App\Models\Horse;
use Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\CustomBookingEmail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Response;

class BokkingController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user(); // Obtén al usuario autenticado
        $bookings = Bokking::where('user_id', $user->id)
                           ->where('date', '>=', now()->toDateString()) // Solo reservas futuras
                           ->orderBy('date', 'asc') // Ordenar por fecha de manera ascendente
                           ->orderBy('time', 'asc') // Luego, ordenar por hora de manera ascendente
                           ->get(); // Obtén las reservas del usuario actual
    
        return view('dashboard', compact('bookings'));
    }
    
    

    public function create()
    {
        $horses = Horse::all();
        return view('Booking.create', compact('horses'));
    }

    public function store(Request $request)
    {
        // Validación de campos
        $request->validate([
            'date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(30)->toDateString(),
            'time' => 'required|in:10:00,11:00,12:00,13:00',
            'horse_id' => 'required|exists:horses,id',
            'email' => 'required|email',
            'comments' => 'nullable|string',

        ]);
    
        // Verificar si las horas del día ya han pasado
        $selectedDate = new \DateTime($request->date);
        $currentTime = now();
        if ($selectedDate->format('Y-m-d') == $currentTime->format('Y-m-d')) {
            $selectedTime = $request->time;
            $currentHour = intval($currentTime->format('H'));
            $selectedHour = intval(substr($selectedTime, 0, 2));
            if ($currentHour >= $selectedHour) {
                return redirect()->back()->withErrors(['time' => 'Las horas de los turnos para hoy ya han terminado. Vuelve mañana.'])->withInput();
            }
        }
    
        // Verificar que la fecha seleccionada sea un sábado o domingo
        $date = new \DateTime($request->date);
        if (!in_array($date->format('N'), [6, 7])) {
            return redirect()->back()->withErrors(['date' => 'Las reservas solo están permitidas los sábados y domingos.'])->withInput();
        }
    
        // Verificar que el caballo no esté enfermo
        $horse = Horse::findOrFail($request->horse_id);
        if ($horse->sick) {
            return redirect()->back()->withErrors(['horse_id' => 'No se puede reservar un caballo que esté enfermo.'])->withInput();
        }
    
        // Verificar que el usuario no tenga una reserva en el mismo turno
        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('user_id', Auth::id())
            ->exists();
        if ($existingBooking) {
            return redirect()->back()->withErrors(['time' => 'Ya tienes una reserva en este turno.'])->withInput();
        }
    
        // Verificar que el caballo no esté reservado en el mismo turno
        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('horse_id', $request->horse_id)
            ->exists();
        if ($existingBooking) {
            return redirect()->back()->withErrors(['horse_id' => 'El caballo ya está reservado en este turno.'])->withInput();
        }
    
        // Verificar que el usuario no tenga otra reserva en las últimas 2 horas del mismo día
        $time = new \DateTime($request->time);
        $earlierTime = (clone $time)->modify('-2 hours')->format('H:i');
    
        $bookingTimes = ['10:00', '11:00', '12:00', '13:00'];
        $currentIndex = array_search($request->time, $bookingTimes);
    
        for ($i = max(0, $currentIndex - 2); $i <= $currentIndex; $i++) {
            if ($i != $currentIndex) {
                $lastBooking = Bokking::where('user_id', Auth::id())
                    ->where('date', $request->date)
                    ->where('time', $bookingTimes[$i])
                    ->where('horse_id', '!=', $request->horse_id)
                    ->exists();
                if ($lastBooking) {
                    return redirect()->back()->withErrors(['time' => 'No puedes reservar un caballo distinto a menos que hayan pasado 2 turnos desde tu última reserva.'])->withInput();
                }
            }
        }
    
        // Pasar los datos de la reserva a la vista payment
        $bookingData = [
            'date' => $request->date,
            'time' => $request->time,
            'horse_id' => $request->horse_id,
            'comments' => $request->comments,
            'email' => $request->email,
            'horse_price' => $horse->price,
        ];
    
        return view('payment', compact('bookingData'));
    }
    

    public function show($id)
    {
        $booking = Bokking::where('user_id', Auth::id())->where('id', $id)->with('horse')->firstOrFail();
        return view('bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = Bokking::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $horses = Horse::all();
        return view('Booking.edit', compact('booking','horses'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(30)->toDateString(),
            'time' => 'required|in:10:00,11:00,12:00,13:00',
            'horse_id' => 'required|exists:horses,id',
            'comments' => 'nullable|string',
        ]);

        $booking = Bokking::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

       // Verificar si las horas del día ya han pasado
        $selectedDate = new \DateTime($request->date);
        $currentTime = now();
        if ($selectedDate->format('Y-m-d') == $currentTime->format('Y-m-d')) {
            $selectedTime = $request->time;
            $currentHour = intval($currentTime->format('H'));
            $selectedHour = intval(substr($selectedTime, 0, 2));
            if ($currentHour >= $selectedHour) {
                return redirect()->back()->withErrors(['time' => 'Las horas de los turnos para hoy ya han terminado. Vuelve mañana.'])->withInput();
            }
        }

        // Verificar que la fecha seleccionada sea un sábado o domingo
        $date = new \DateTime($request->date);
        if (!in_array($date->format('N'), [6, 7])) {
            return redirect()->back()->withErrors(['date' => 'Las reservas solo están permitidas los sábados y domingos.'])->withInput();
        }

        // Verificar que el caballo no esté enfermo
        $horse = Horse::findOrFail($request->horse_id);
        if ($horse->sick) {
            return redirect()->back()->withErrors(['horse_id' => 'No se puede reservar un caballo que esté enfermo.'])->withInput();
        }

        // Verificar que no haya más de 5 reservas en el mismo turno
        $bookingsCount = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->count();
        if ($bookingsCount >= 5) {
            return redirect()->back()->withErrors(['time' => 'El turno ya está completo.'])->withInput();
        }

        // Verificar que el caballo no esté reservado en el mismo turno
        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('horse_id', $request->horse_id)
            ->exists();
        if ($existingBooking) {
            return redirect()->back()->withErrors(['horse_id' => 'El caballo ya está reservado en este turno.'])->withInput();
        }

   
        
        // Verificar que el usuario no tenga otra reserva en las últimas 2 horas del mismo día
        $time = new \DateTime($request->time);
        $earlierTime = (clone $time)->modify('-2 hours')->format('H:i');

        $bookingTimes = ['10:00', '11:00', '12:00', '13:00'];
        $currentIndex = array_search($request->time, $bookingTimes);

        for ($i = max(0, $currentIndex - 2); $i <= $currentIndex; $i++) {
            if ($i != $currentIndex) {
                $lastBooking = Bokking::where('user_id', Auth::id())
                    ->where('date', $request->date)
                    ->where('time', $bookingTimes[$i])
                    ->where('horse_id', '!=', $request->horse_id)
                    ->exists();
                if ($lastBooking) {
                    return redirect()->back()->withErrors(['time' => 'No puedes reservar un caballo distinto a menos que hayan pasado 2 turnos desde tu última reserva.'])->withInput();
                }
            }
        }
        

        // Actualizar la reserva
        $booking->fill($request->all());
        $booking->save();

        return redirect()->route('bookings.index')->with('success', 'Reserva actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $booking = Bokking::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Reserva eliminada exitosamente.');
    }

    public function showPdf($id)
    {
        $booking = Bokking::findOrFail($id);

        // Cargar la vista del detalle de la reserva para el PDF
        $html = view('Booking.show_pdf', compact('booking'))->render();

        // Crear una instancia de Dompdf con opciones
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        // Cargar el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderizar el PDF
        $dompdf->render();

        // Obtener el contenido del PDF
        $pdfContent = $dompdf->output();

        // Enviar la respuesta con el archivo PDF
        $response = Response::make($pdfContent);
        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'inline; filename="booking_details_' . $id . '.pdf"');

        return $response;
    }
}