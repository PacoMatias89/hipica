<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Payment;
use App\Models\Horse;
use App\Models\Bokking;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    public function createPaymentIntent(Request $request)
    {
        $horse = Horse::find($request->input('horse_id'));
        $horsePrice = str_replace(',', '.', $horse->price) * 100; // Convertir a centavos
    
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $horsePrice,
                'currency' => 'usd',
                'metadata' => [
                    'user_id' => Auth::id(),
                    'horse_id' => $request->input('horse_id'),
                    'date' => $request->input('date'),
                    'time' => $request->input('time'),
                    'comments' => $request->input('comments'),
                ],
            ]);
    
            // Agregar registro para depuración
            \Log::info('PaymentIntent creado con ID: ' . $paymentIntent->id);
    
            return response()->json(['clientSecret' => $paymentIntent->client_secret, 'id' => $paymentIntent->id]); // Agrega el ID del PaymentIntent a la respuesta
        } catch (\Exception $e) {
            \Log::error('Error al crear PaymentIntent: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function confirm(Request $request)
    {
        $paymentIntentId = $request->input('payment_intent_id');
    
        try {
            \Log::info('Intentando confirmar PaymentIntent con ID: ' . $paymentIntentId);
    
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
    
            if ($paymentIntent->status == 'succeeded') {
                \Log::info('El PaymentIntent ya ha sido confirmado y completado');
                return response()->json(['success' => 'Payment already confirmed!']);
            } else {
                $paymentIntent->confirm();
    
                if ($paymentIntent->status == 'succeeded') {
                    $this->store_payment([
                        'payment_id' => $paymentIntent->id,
                        'payer_email' => $paymentIntent->charges->data[0]->billing_details->email,
                        'amount' => $paymentIntent->amount / 100,
                        'currency' => strtoupper($paymentIntent->currency),
                        'payment_status' => $paymentIntent->status,
                        'date' => $paymentIntent->metadata->date,
                        'time' => $paymentIntent->metadata->time,
                        'horse_id' => $paymentIntent->metadata->horse_id,
                        'comments' => $paymentIntent->metadata->comments,
                        'user_id' => $paymentIntent->metadata->user_id,
                    ]);
    
                    return response()->json(['success' => 'Payment successful!']);
                } else {
                    return response()->json(['error' => 'Payment failed!']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al confirmar PaymentIntent: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    

    public function store_payment($arr_data)
    {
        $isPaymentExist = Payment::where('payment_id', $arr_data['payment_id'])->first();

        if (!$isPaymentExist)
        {
            $payment = new Payment;
            $payment->payment_id = $arr_data['payment_id'];
            $payment->payer_email = $arr_data['payer_email'];
            $payment->amount = $arr_data['amount'];
            $payment->currency = $arr_data['currency'];
            $payment->payment_status = $arr_data['payment_status'];
            $payment->user_id = $arr_data['user_id'];
            $payment->save();

            // Crear reserva asociada al pago
            $booking = new Bokking;
            $booking->date = $arr_data['date'] ?? null; // Establecer como nulo por defecto si no está presente
            $booking->time = $arr_data['time'] ?? null; // Establecer como nulo por defecto si no está presente
            $booking->horse_id = $arr_data['horse_id'] ?? null; // Establecer como nulo por defecto si no está presente
            $booking->user_id = $arr_data['user_id']; // Establecer el id del usuario autenticado
            $booking->comments = $arr_data['comments'] ?? ''; // Establecer como vacío por defecto si no está presente
            $booking->save();

            // Enviar correo electrónico de confirmación utilizando la notificación CustomBookingEmail
            $user = User::find($arr_data['user_id']);
            if ($user) {
                $user->notify(new \App\Notifications\CustomBookingEmail($booking));
            }
        }
    }

    // Agrega esto a tu PaymentController
    public function retrievePaymentIntent(Request $request)
    {
        $paymentIntentId = $request->input('payment_intent_id');

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            return response()->json(['status' => $paymentIntent->status, 'paymentIntent' => $paymentIntent]);
        } catch (\Exception $e) {
            \Log::error('Error al recuperar PaymentIntent: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
