<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    private $activationLink;

    /**
     * Create a new message instance.
     *
     * @param string $activationLink
     * @return void
     */
    public function __construct(string $activationLink)
    {
        $this->activationLink = $activationLink;
    }
    public function build()
    {
        return $this->from('no-reply@example.com', 'Example App') // Configura el remitente aquí
                    ->view('emails.activate_account')
                    ->with('activationLink', $this->activationLink);
    }
    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Account Activation Mail',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.account_activation',
            with: [
                'activationLink' => $this->activationLink
            ]
        );
    }
    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
