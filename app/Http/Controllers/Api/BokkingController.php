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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Cargar las reservas del usuario autenticado junto con la relación con Horse
        $bookings = Bokking::with('horse:id,name')
            ->where('user_id', $user->id)
            ->get();

        // Transformar la respuesta para incluir el nombre del caballo
        $bookings = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'date' => $booking->date,
                'time' => $booking->time,
                'comments' => $booking->comments,
                'user_id' => $booking->user_id,
                'horse_name' => $booking->horse->name,
            ];
        });

        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'time' => 'required',
            'comments' => 'required',
            'horse_id' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $horse = Horse::find($request->horse_id);
    
        if ($horse && $horse->sick) {
            return response()->json(['ERROR' => 'El caballo está enfermo'], 400);
        }
    
        $existingBooking = BoKking::where('date', $request->date)
            ->where('time', $request->time)
            ->count();
    
        if ($existingBooking >= 5) {
            return response()->json(['ERROR' => 'El turno ya está completo.'], 400);
        }
    
       // Verificar que el caballo no esté reservado en el mismo turno
        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('horse_id', $request->horse_id)
            ->exists();

        if ($existingBooking) {
            return response()->json(['ERROR' => 'El caballo ya está reservado en este turno.'], 400);
        }
    
        // Crear la reserva
        $booking = Bokking::create([
            'date' => $request->date,
            'time' => $request->time,
            'comments' => $request->comments,
            'user_id' => $user->id, // Obtener el user_id del usuario autenticado
            'horse_id' => $request->horse_id,
        ]);

      
       
    
        // Cargar la relación con Horse para incluir el nombre en la respuesta
        $booking->load('horse:id,name');
    
        // Crear la respuesta JSON sin incluir el user_id
        $response = [
            'id' => $booking->id,
            'date' => $booking->date,
            'time' => $booking->time,
            'comments' => $booking->comments,
            'horse_name' => $booking->horse->name, // Incluye el nombre del caballo
        ];

         // Enviar correo electrónico de confirmación utilizando la notificación CustomBookingEmail
         $user->notify(new CustomBookingEmail($booking));
    
        return response()->json($response, 201);
    }

    /**
     * Update the specified resource in storage.
     */
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
    
        // Comprobar si el caballo está enfermo
        $horse = Horse::find($request->horse_id);
        if ($horse && $horse->is_sick) {
            return response()->json(['error' => 'No se puede reservar un caballo enfermo'], 400);
        }
    
        // Comprobar disponibilidad
        $existingBooking = Bokking::where('date', $request->date)
            ->where('time', $request->time)
            ->where('id', '!=', $booking->id) // Excluir la reserva actual
            ->count();
        if ($existingBooking >= 5) {
            return response()->json(['error' => 'No hay disponibilidad en este horario'], 400);
        }
    
        // Actualizar la reserva
        $booking->update([
            'date' => $request->date,
            'time' => $request->time,
            'comments' => $request->comments,
            'horse_id' => $request->horse_id,
        ]);
    
        // Cargar la relación con Horse para incluir el nombre en la respuesta
        $booking->load('horse:id,name');
    
        // Crear la respuesta JSON sin incluir el user_id
        $response = [
            'id' => $booking->id,
            'date' => $booking->date,
            'time' => $booking->time,
            'comments' => $booking->comments,
            'horse_name' => $booking->horse->name, // Incluye el nombre del caballo
        ];
    
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
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