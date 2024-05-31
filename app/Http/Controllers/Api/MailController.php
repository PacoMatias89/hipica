<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Notifications\CustomVerifyEmail;
use Validator;
use Exception;

class MailController extends BaseController
{
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' =>'required|email',
            'from' =>'required|email',
            'subject' =>'required',
            'message' =>'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        } else {
            try {
                $emailData = $request->all();
                $to = $emailData['email'];
                $from = $emailData['from'];
                $subject = $emailData['subject'];
                $message = $emailData['message'];

                // Obtener el usuario autenticado
                $user = $request->user();

                // Enviar la notificación de verificación de correo electrónico
                $user->notify(new CustomVerifyEmail($user));

                return $this->sendResponse('', 'Email has been sent to ' . $to);

            } catch(Exception $e){
                return $this->sendError('Error al enviar el email', $e->getMessage(), 500);
            }
        }
    }
}
