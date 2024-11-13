<?php

namespace App\Mail;

use App\Models\User;
use Dotenv\Util\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Attachment;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */




     private $user;
     private $path;
 
     public function __construct(User $user,String $path)
     {
         $this->user = $user;
         $this->path = $path;

     }
 
     public function build()
     {
         return $this->from('no-reply@example.com', 'Example App') // Configura el remitente aquí
                     ->view('emails.dynamic_email')
                     ->with([
                         'name' => $this->user->name, // Pasa el nombre a la vista
                         'email' => $this->user->email, // Pasa el correo a la vista
                     ]);
     }
     //C:\Users\Dante\elaravel2\storage\app\public\images
     //C:\Users\Dante\elaravel2\storage\app/images/6qp0KpUp4z3fAImpv5i79QI8TW4NywB2yxRgR4Kw.jpg
    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Dynamic Email',
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
            view: 'emails.dynamic_email',  // nombre de la vista
            with: [
                'name' => $this->user->name, // Pasa el nombre a la vista
                'email' => $this->user->email, // Pasa el correo a la vista
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
        return [
        
            Attachment::fromPath($this->path),
        ];
    }
}
