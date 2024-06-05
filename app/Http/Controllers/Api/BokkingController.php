<?php

namespace App\Http\Controllers\Api;

use App\Models\Bokking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Models\Horse;
use Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\CustomBookingEmail;

class BokkingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentDate = now();

        // Obtener solo las reservas futuras, ordenadas de más recientes a más lejanas
        $bookings = Bokking::where('user_id', $user->id)
                           ->where('date', '>=', now()->toDateString()) // Solo reservas futuras
                           ->orderBy('date', 'asc') // Ordenar por fecha de manera ascendente
                           ->orderBy('time', 'asc') // Luego, ordenar por hora de manera ascendente
                           ->get(); // Obtén las reservas del usuario actual
        $bookings = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'date' => $booking->date,
                'time' => $booking->time,
                'comments' => $booking->comments,
                'user_id' => $booking->user_id,
                'horse_name' => $booking->horse->name,
                'horse_price' => $booking->horse->price,
            ];
        });

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'comments' => 'required|string',
            'horse_id' => 'required|exists:horses,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horse = Horse::find($request->horse_id);
        if ($horse && $horse->sick) {
            return response()->json(['error' => 'El caballo está enfermo'], 400);
        }

        $existingBookingCount = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->count();
        if ($existingBookingCount >= 5) {
            return response()->json(['error' => 'El turno ya está completo.'], 400);
        }

        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('horse_id', $request->horse_id)
            ->exists();
        if ($existingBooking) {
            return response()->json(['error' => 'El caballo ya está reservado en este turno.'], 400);
        }

        $booking = Bokking::create([
            'date' => $request->date,
            'time' => $request->time,
            'comments' => $request->comments,
            'user_id' => $user->id,
            'horse_id' => $request->horse_id,
        ]);

        $booking->load('horse:id,name,price');

        $response = [
            'id' => $booking->id,
            'date' => $booking->date,
            'time' => $booking->time,
            'comments' => $booking->comments,
            'horse_name' => $booking->horse->name,
            'horse_price' => $booking->horse->price,
        ];

        // Enviar correo electrónico de confirmación utilizando la notificación CustomBookingEmail
        $user->notify(new CustomBookingEmail($booking));

        return response()->json($response, 201);
    }

    public function update(Request $request, $id)
    {
        $booking = Bokking::find($id);
    
        if (is_null($booking)) {
            return response()->json(['message' => 'Bokking not found'], 404);
        }
    
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'comments' => 'nullable|string',
            'horse_id' => 'required|exists:horses,id',
        ]);
    
        $horse = Horse::find($request->horse_id);
        if ($horse && $horse->is_sick) {
            return response()->json(['error' => 'No se puede reservar un caballo enfermo'], 400);
        }
    
        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('id', '!=', $booking->id)
            ->count();
        if ($existingBooking >= 5) {
            return response()->json(['error' => 'No hay disponibilidad en este horario'], 400);
        }
    
        $booking->update([
            'date' => $request->date,
            'time' => $request->time,
            'comments' => $request->comments,
            'horse_id' => $request->horse_id,
        ]);
    
        $booking->load('horse:id,name');
    
        $response = [
            'id' => $booking->id,
            'date' => $booking->date,
            'time' => $booking->time,
            'comments' => $booking->comments,
            'horse_name' => $booking->horse->name,
        ];
    
        return response()->json($response, 200);
    }

    public function destroy($id)
    {
        $booking = Bokking::find($id);

        if (is_null($booking)) {
            return response()->json(['message' => 'Bokking not found'], 404);
        }

        $booking->delete();

        return response()->json(['message' => 'Bokking deleted successfully'], 200);
    }
}
