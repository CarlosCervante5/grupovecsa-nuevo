<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\CustomerAppointment;
use App\Models\CustomerVehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ValuationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $vehicle;
    public $appointment;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer, CustomerVehicle $vehicle, CustomerAppointment $appointment)
    {
        $this->customer = $customer;
        $this->vehicle = $vehicle;
        $this->appointment = $appointment;
        
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de valuación',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.external_valuation',
            with: [
                'app' => env('APP_NAME',''),
                'name' => $this->customer->name .' '. $this->customer->last_name,
                'brand' => $this->vehicle->brand_name,
                'model' => $this->vehicle->model_name,
                'scheduled_date' => $this->appointment->scheduled_date,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
