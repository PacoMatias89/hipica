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
    $bookings = Bokking::where('user_id', $user->id)->get(); // Obtén las reservas del usuario actual

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
            'date' => 'required|date|after_or_equal:today|before_or_equal:'.now()->addDays(30)->toDateString(),
            'time' => 'required|in:10:00,11:00,12:00,13:00',
            'horse_id' => 'required|exists:horses,id',
            'comments' => 'nullable|string',
        ]);
    
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
    
            // Crear la reserva
        $booking = new Bokking($request->all());
        $booking->user_id = Auth::id();
        $booking->save();

        // Enviar correo electrónico de confirmación utilizando la notificación CustomBookingEmail
        $user = Auth::user();
        $user->notify(new CustomBookingEmail($booking));

        return redirect()->route('bookings.index')->with('success', 'Reserva creada exitosamente.');
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
        return view('Booking.edit', compact('booking', 'horses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today|before_or_equal:'.now()->addDays(30)->toDateString(),
            'time' => 'required|in:10:00,11:00,12:00,13:00',
            'horse_id' => 'required|exists:horses,id',
            'comments' => 'nullable|string',
        ]);

        $booking = Bokking::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

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

        $booking->fill($request->all());
        $booking->save();

        return redirect('/dashboard');
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