<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Omnipay\Omnipay;
use App\Models\Payment;
use App\Models\Horse;
use App\Models\Bokking;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Notifications\CustomBookingEmail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Auth;
use Illuminate\Support\Facades\Response;


class PaymentController extends Controller
{
    public $gateway;
    public $completePaymentUrl;

    public function __construct()
    {
        $this->gateway = Omnipay::create('Stripe\PaymentIntents');
        $this->gateway->setApiKey(env('STRIPE_SECRET_KEY'));
        $this->completePaymentUrl = url('confirm');
    }

    public function index()
    {
        return view('payment');
    }

    public function charge(Request $request)
    {
        if ($request->input('stripeToken'))
        {
            $token = $request->input('stripeToken');
            
            // Recuperar el precio del caballo
            $horse = Horse::find($request->input('horse_id'));
            $horsePrice = str_replace(',', '.', $horse->price);
    
            $response = $this->gateway->authorize([
                'amount' => $horsePrice , 
                'currency' => config('services.stripe.currency'),
                'description' => 'Payment for horse reservation',
                'token' => $token,
                'returnUrl' => $this->completePaymentUrl,
                'confirm' => true,
            ])->send();
    
            if ($response->isSuccessful())
            {
                $response = $this->gateway->capture([
                    'amount' => $request->input('amount'),
                    'currency' => env('STRIPE_CURRENCY'),
                    'paymentIntentReference' => $response->getPaymentIntentReference(),
                ])->send();
    
                $arr_payment_data = $response->getData();
    
                // Obtener el ID del usuario autenticado a partir del correo electrónico
                $user = User::where('email', $request->input('email'))->first();
    
                $this->store_payment([
                    'payment_id' => $arr_payment_data['id'],
                    'payer_email' => $request->input('email'),
                    'amount' => $horsePrice,
                    'currency' => config('services.stripe.currency'),
                    'payment_status' => $arr_payment_data['status'],
                    'date' => $request->input('date'), // Obtener fecha desde la solicitud
                    'time' => $request->input('time'), // Obtener hora desde la solicitud
                    'horse_id' => $request->input('horse_id'), // Obtener id del caballo desde la solicitud
                    'comments' => $request->input('comments'), // Obtener comentarios desde la solicitud
                    'user_id' => $user->id, // ID del usuario autenticado
                ]);

    
                // Establecer el mensaje de éxito en la sesión
                $request->session()->flash('success', '¡Pago exitoso! Tu pagaste' . $horsePrice . '  ' . env('STRIPE_CURRENCY').'. Te hemos enviado un correo electrónico con los detalles de tu reserva');

                // Redireccionar a la página de reservas después de mostrar el mensaje
                return redirect()->route('dashboard');
            }
            elseif ($response->isRedirect())
            {
                $response->redirect();
            }
            else
            {
                // Establecer el mensaje de error en la sesión
                $request->session()->flash('error', $response->getMessage());
                
                // Redireccionar a la página de pagos con el mensaje de error
                return redirect()->route('payment');
            }
        }
    }

    public function confirm(Request $request)
    {
        $payment_id = $request->input('payment_intent');

        if ($payment_id)
        {
            $response = $this->gateway->confirm([
                'paymentIntentReference' => $payment_id,
            ])->send();

            if ($response->isSuccessful())
            {
                $arr_payment_data = $response->getData();

                // Obtener el ID del usuario autenticado a partir del correo electrónico
                $user = User::where('email', $arr_payment_data['charges']['data'][0]['billing_details']['email'])->first();

                $this->store_payment([
                    'payment_id' => $arr_payment_data['id'],
                    'payer_email' => $arr_payment_data['charges']['data'][0]['billing_details']['email'],
                    'amount' => $arr_payment_data['amount'] / 1000,
                    'currency' => strtoupper($arr_payment_data['currency']),
                    'payment_status' => $arr_payment_data['status'],
                    'date' => $request->input('date'), // Obtener fecha desde la solicitud
                    'time' => $request->input('time'), // Obtener hora desde la solicitud
                    'horse_id' => $request->input('horse_id'), // Obtener id del caballo desde la solicitud
                    'comments' => $request->input('comments'), // Obtener comentarios desde la solicitud
                    'user_id' => $user->id, // ID del usuario autenticado
                ]);

                return redirect()->route('dashboard')->with('success', 'Payment successful! You paid ' . ($arr_payment_data['amount'] / 1000) . ' ' . strtoupper($arr_payment_data['currency']));
            }
            else
            {
                return redirect()->route('payment')->with('error');
            }
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
        $user = User::find($arr_data['user_id']); // Cambiado a 'user_id' en lugar de 'payer_email'
        if ($user) {
            $user->notify(new CustomBookingEmail($booking));
        } else{
            //Usuario no exite
            return redirect()->route('payment')->with('usuario no existe');
        }
    }
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
        $response->header('Content-Disposition', 'inline; filename="Detallas_Reserva' . $id . '.pdf"');

        return $response;
    }
    

}