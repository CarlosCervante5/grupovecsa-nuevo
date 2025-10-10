<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RewardNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $moto;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer, String $moto, String $year)
    {
        $this->customer = $customer;
        $this->moto = $moto.' '.$year;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo rider registrado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reward_notification',
            with: [
                'app' => env('APP_NAME',''),
                'name' => $this->customer->name .' '. $this->customer->last_name,
                'phone' => $this->customer->phone_1,
                'email' => $this->customer->email_1,
                'moto' => $this->moto,
                'dealership' => $this->customer->origin_agency
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
