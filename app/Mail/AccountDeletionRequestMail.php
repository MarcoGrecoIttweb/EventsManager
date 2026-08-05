<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $usersAdminUrl;

    public function __construct(User $user, string $usersAdminUrl)
    {
        $this->user = $user;
        $this->usersAdminUrl = $usersAdminUrl;
    }

    public function build()
    {
        $app = config('app.name', 'Excursio');
        $nick = $this->user->username;

        return $this->subject($app.' — Richiesta di cancellazione account: '.$nick)
            ->view('emails.account-deletion-request');
    }
}