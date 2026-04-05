<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationPendingUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $registeredUser;

    public function __construct(User $registeredUser)
    {
        $this->registeredUser = $registeredUser;
    }

    public function build()
    {
        $app = config('app.name', 'Excursio');

        return $this->subject($app.' — Registrazione ricevuta, in attesa di approvazione')
            ->view('emails.registration-pending-user');
    }
}
