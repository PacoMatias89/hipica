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

class CustomBookingEmail extends Notification
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
        // Generate the PDF content and save it to temporary storage
        $pdfPath = $this->generatePdf();

        // Attach the PDF to the email using its temporary path
        return (new MailMessage)
            ->subject('Confirmación de reserva')
            ->line('Se ha realizado una reserva con éxito.')
            ->line('Detalles de la reserva:')
            ->line('Fecha: ' . $this->booking->date)
            ->line('Hora: ' . $this->booking->time)
            ->line('Comentarios: ' . $this->booking->comments)
            ->line('Caballo: ' . $this->booking->horse->name)
            ->line('Precio del caballo: '. $this->booking->horse->price)
            ->attach($pdfPath, ['as' => 'booking_details.pdf'])
            ->line('¡Gracias por utilizar nuestro servicio!');
    }

    /**
     * Generate the PDF for the booking details and save it to temporary storage.
     *
     * @return string
     */
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
        $pdfPath = storage_path('app/public/booking_details.pdf');
        file_put_contents($pdfPath, $pdfContent);

        // Return the path to the saved PDF
        return $pdfPath;
    }
}
