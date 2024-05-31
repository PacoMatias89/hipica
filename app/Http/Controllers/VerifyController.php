<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        // Lógica de verificación de correo electrónico...
        // Por ejemplo, podrías verificar el token de verificación aquí

        // Obtener el usuario autenticado
        $user = Auth::user();

        // Marcar el correo electrónico del usuario como verificado
        $user->markEmailAsVerified();

        // Configurar la sesión
        $request->session()->flash('email_verified', true);

        return redirect()->route('login');
    }
}