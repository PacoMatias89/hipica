<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bokking;
use App\Models\Horse;
use Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\CustomBookingEditEmail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Response;
use App\Notifications\CustomBookingDeleteEmail;

class BokkingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $bookings = Bokking::where('user_id', $user->id)
                           ->where('date', '>=', now()->toDateString())
                           ->orderBy('date', 'asc')
                           ->orderBy('time', 'asc')
                           ->get();

        return view('dashboard', compact('bookings'));
    }

    public function create()
    {
        $horses = Horse::all();
        return view('Booking.create', compact('horses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(30)->toDateString(),
            'time' => 'required|in:10:00,11:00,12:00,13:00',
            'horse_id' => 'required|exists:horses,id',
            'email' => 'required|email',
            'comments' => 'nullable|string',
        ]);

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

        $date = new \DateTime($request->date);
        if (!in_array($date->format('N'), [6, 7])) {
            return redirect()->back()->withErrors(['date' => 'Las reservas solo están permitidas los sábados y domingos.'])->withInput();
        }

        $horse = Horse::findOrFail($request->horse_id);
        if ($horse->sick) {
            return redirect()->back()->withErrors(['horse_id' => 'No se puede reservar un caballo que esté enfermo.'])->withInput();
        }

        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('user_id', Auth::id())
            ->exists();
        if ($existingBooking) {
            return redirect()->back()->withErrors(['time' => 'Ya tienes una reserva en este turno.'])->withInput();
        }

        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('horse_id', $request->horse_id)
            ->exists();
        if ($existingBooking) {
            return redirect()->back()->withErrors(['horse_id' => 'El caballo ya está reservado en este turno.'])->withInput();
        }

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
        $currentDateTime = now();
        $bookingDateTime = \Carbon\Carbon::parse($booking->date . ' ' . $booking->time);

        if ($currentDateTime->greaterThanOrEqualTo($bookingDateTime)) {
            return redirect()->route('bookings.index')->withErrors(['message' => 'No se puede editar una reserva pasada.']);
        }

        $horses = Horse::all();
        return view('Booking.edit', compact('booking', 'horses'));
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
        $currentDateTime = now();
        $bookingDateTime = \Carbon\Carbon::parse($booking->date . ' ' . $booking->time);

        if ($currentDateTime->greaterThanOrEqualTo($bookingDateTime)) {
            return redirect()->route('bookings.index')->withErrors(['message' => 'No se puede actualizar una reserva pasada.']);
        }

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

        $date = new \DateTime($request->date);
        if (!in_array($date->format('N'), [6, 7])) {
            return redirect()->back()->withErrors(['date' => 'Las reservas solo están permitidas los sábados y domingos.'])->withInput();
        }

        $horse = Horse::findOrFail($request->horse_id);
        if ($horse->sick) {
            return redirect()->back()->withErrors(['horse_id' => 'No se puede reservar un caballo que esté enfermo.'])->withInput();
        }

        $bookingsCount = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->count();
        if ($bookingsCount >= 5) {
            return redirect()->back()->withErrors(['time' => 'El turno ya está completo.'])->withInput();
        }

        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('horse_id', $request->horse_id)
            ->exists();
        if ($existingBooking) {
            return redirect()->back()->withErrors(['horse_id' => 'El caballo ya está reservado en este turno.'])->withInput();
        }

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

        $booking->fill($request->all());
        $booking->save();
        
        //Enviamos el email de confirmación de la actualización
        $user = Auth::user();
        $user->notify(new CustomBookingEditEmail($booking));

        return redirect()->route('bookings.index')->with('success', 'Reserva actualizada exitosamente. Se ha enviado un correo electrónico con los detalles de la reserva.');
    }

    public function destroy($id)
    {
        $booking = Bokking::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $currentDateTime = now();
        $bookingDateTime = \Carbon\Carbon::parse($booking->date . ' ' . $booking->time);

        if ($currentDateTime->greaterThanOrEqualTo($bookingDateTime)) {
            return redirect()->route('bookings.index')->withErrors(['message' => 'No se puede eliminar una reserva pasada.']);
        }

        //Enviamos el email de confirmación de la eliminación
        $user = Auth::user();
        $user->notify(new CustomBookingDeleteEmail($id));

        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Reserva eliminada exitosamente. Se ha enviado un correo electrónico confirmando la eliminación de la reserva.');
    }

    public function showPdf($id)
    {
        $booking = Bokking::findOrFail($id);

        $html = view('Booking.show_pdf', compact('booking'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);

        $dompdf->render();

        $pdfContent = $dompdf->output();

        $response = Response::make($pdfContent);
        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'inline; filename="Detalles_reserva' . $id . '.pdf"');

        return $response;
    }
}

