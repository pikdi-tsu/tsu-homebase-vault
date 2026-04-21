<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendResetPasswordToken extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $name;
    public $email;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $email, $name)
    {
        $this->token = $token;
        $this->name = $name;
        $this->email = $email;

        // Ambil URL Front-end dari .env (misal: FRONTEND_URL=http://localhost:3000)
        $baseUrl = config('app.frontend_url');

        // Buat link lengkap ke halaman reset password di front-end
        $this->resetUrl = $baseUrl . '/reset-password/' . $this->token . '?email=' . urlencode($this->email);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Link Reset Password Anda')
            ->view('emails.auth.password-reset-link', [
                'resetUrl' => $this->resetUrl,
                'name' => $this->name
            ]);
    }

    /**
     * Get the message envelope.
     */
//    public function envelope(): Envelope
//    {
//        return new Envelope(
//            subject: 'Send Reset Password Token',
//        );
//    }

    /**
     * Get the message content definition.
     */
//    public function content(): Content
//    {
//        return new Content(
//            view: 'auth.reset-password',
//        );
//    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
//    public function attachments(): array
//    {
//        return [];
//    }
}
