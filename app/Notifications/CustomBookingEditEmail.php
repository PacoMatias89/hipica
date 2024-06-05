<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Bokking;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class CustomBookingEditEmail extends Notification
{
    use Queueable;
    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bokking $booking)
    {
        $this->booking = $booking;
    }
   

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $pdfPath = $this->generatePdf();

        return (new MailMessage)
            ->subject('Actualización  de reserva')
            ->line('Se ha realizado una actualización con éxito.')
            ->line('Detalles de la reserva:')
            ->line('Fecha: ' . $this->booking->date)
            ->line('Hora: ' . $this->booking->time)
            ->line('Comentarios: ' . $this->booking->comments)
            ->line('Caballo: ' . $this->booking->horse->name)
            ->line('Precio del caballo: '. $this->booking->horse->price)
            ->attach($pdfPath, ['as' => 'Detalles_actualizados_reserva.pdf'])
            ->line('¡Gracias por utilizar nuestro servicio!');
    }

    protected function generatePdf(): string
    {
        // Load the HTML content from the view
        $html = view('Booking.show_pdf', ['booking' => $this->booking])->render();

        // Create Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial'); // Set the default font if needed

        // Create a Dompdf instance with options
        $dompdf = new Dompdf($options);

        // Load the HTML content into Dompdf
        $dompdf->loadHtml($html);

        // Render the PDF
        $dompdf->render();

        // Save the PDF to temporary storage
        $pdfContent = $dompdf->output();
        $pdfPath = storage_path('app/public/Detalles_reserva_.pdf');
        file_put_contents($pdfPath, $pdfContent);

        // Return the path to the saved PDF
        return $pdfPath;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
