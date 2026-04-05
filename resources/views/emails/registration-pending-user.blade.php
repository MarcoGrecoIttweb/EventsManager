<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrazione ricevuta</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(45deg, #6c5ce7, #a29bfe); color: #fff; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 24px; border-radius: 0 0 10px 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:1.35rem;">Ciao@if(trim((string) $registeredUser->nome) !== ''), {{ $registeredUser->nome }}@endif!</h1>
    </div>
    <div class="content">
        <p><strong>La tua registrazione su {{ config('app.name', 'Excursio') }} è andata a buon fine.</strong></p>
        <p>
            Il tuo profilo è stato creato con questi dati principali:
        </p>
        <ul>
            <li><strong>Nome utente (nickname):</strong> {{ $registeredUser->username }}</li>
            <li><strong>Nome:</strong> {{ trim((string) $registeredUser->nome) !== '' ? $registeredUser->nome : '—' }}</li>
            <li><strong>Cognome:</strong> {{ trim((string) $registeredUser->cognome) !== '' ? $registeredUser->cognome : '—' }}</li>
        </ul>
        <p>
            <strong>Il tuo account non è ancora attivo:</strong> un amministratore deve approvarlo prima che tu possa accedere.
            Non serve registrarti di nuovo. Quando il profilo sarà stato esaminato potrai effettuare il login con il nickname e la password che hai scelto.
        </p>
        <p style="margin-bottom:0;">Grazie per esserti iscritto.</p>
    </div>
    <div class="footer">
        Messaggio automatico da {{ config('app.name', 'Excursio') }} — non rispondere se l’indirizzo è solo per notifiche.
    </div>
</body>
</html>
