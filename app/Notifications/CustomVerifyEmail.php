<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;

class CustomVerifyEmail extends VerifyEmailNotification
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
        ->subject(Lang::get('Verifica tu dirección de correo electrónico'))
        ->greeting(Lang::get('¡Hola, :name!', ['name' => $notifiable->name]))
        ->line(Lang::get('Haz clic en el botón a continuación para verificar tu dirección de correo electrónico.'))
        ->action(Lang::get('Verificar dirección de correo'), $verificationUrl)
        ->line(Lang::get('Si no creaste una cuenta, no es necesario realizar ninguna otra acción.'))
        ->line(Lang::get('Agradecemos tu confianza en Caballos para Disfrutar para tu experiencia ecuestre.'));

    }
}