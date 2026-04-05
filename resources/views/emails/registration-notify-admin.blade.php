<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuova iscrizione</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2d3436; color: #fff; padding: 18px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 24px; border-radius: 0 0 10px 10px; }
        .btn { display: inline-block; padding: 12px 22px; background: #6c5ce7; color: #fff !important; text-decoration: none; border-radius: 8px; margin-top: 12px; }
        .footer { text-align: center; margin-top: 16px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:1.2rem;">Nuova iscrizione da approvare</h1>
    </div>
    <div class="content">
        <p>Un nuovo utente si è registrato su <strong>{{ config('app.name', 'Excursio') }}</strong> e attende l’abilitazione.</p>
        <ul>
            <li><strong>Username (nickname):</strong> {{ $registeredUser->username }}</li>
            <li><strong>Nome:</strong> {{ trim((string) $registeredUser->nome) !== '' ? $registeredUser->nome : '—' }}</li>
            <li><strong>Cognome:</strong> {{ trim((string) $registeredUser->cognome) !== '' ? $registeredUser->cognome : '—' }}</li>
            <li><strong>Email:</strong> {{ $registeredUser->email }}</li>
        </ul>
        <p style="margin-bottom:8px;">Accedi alla gestione utenti per approvare o rifiutare la richiesta.</p>
        <p>
            <a class="btn" href="{{ $usersAdminUrl }}">Apri gestione utenti</a>
        </p>
    </div>
    <div class="footer">
        Notifica automatica — {{ config('app.name', 'Excursio') }}
    </div>
</body>
</html>
