<?php

namespace App\Mail;

use App\Models\ServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceBookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu reserva fue agendada! — ' . $this->booking->reference_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service_booking_confirmed',
        );
    }
}
