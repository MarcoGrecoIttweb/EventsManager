<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $message, User $user)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.newsletter')
            ->with([
                'content' => $this->message,
                'user' => $this->user
            ]);
    }
}
