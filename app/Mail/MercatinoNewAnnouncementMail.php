<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MercatinoNewAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array<string, mixed> */
    public array $annuncio;

    /** @var array<string, mixed> */
    public array $autore;

    public string $mercatinoUrl;

    /**
     * @param array<string, mixed> $annuncio
     * @param array<string, mixed> $autore
     */
    public function __construct(array $annuncio, array $autore, string $mercatinoUrl)
    {
        $this->annuncio = $annuncio;
        $this->autore = $autore;
        $this->mercatinoUrl = $mercatinoUrl;
    }

    public function build()
    {
        $app = config('app.name', 'Excursio');
        $titolo = trim((string) ($this->annuncio['titolo'] ?? ''));
        $autore = trim((string) ($this->autore['username'] ?? ''));

        $subject = $app . ' — Nuovo annuncio Mercatino';
        if ($titolo !== '') {
            $subject .= ': ' . $titolo;
        }
        if ($autore !== '') {
            $subject .= ' (' . $autore . ')';
        }

        return $this->subject($subject)
            ->view('emails.mercatino-new-announcement');
    }
}

