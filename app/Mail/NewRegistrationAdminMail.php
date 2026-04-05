<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewRegistrationAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $registeredUser;

    public string $usersAdminUrl;

    public function __construct(User $registeredUser, string $usersAdminUrl)
    {
        $this->registeredUser = $registeredUser;
        $this->usersAdminUrl = $usersAdminUrl;
    }

    public function build()
    {
        $app = config('app.name', 'Excursio');
        $nick = $this->registeredUser->username;

        return $this->subject($app.' — Nuova iscrizione da approvare: '.$nick)
            ->view('emails.registration-notify-admin');
    }
}
