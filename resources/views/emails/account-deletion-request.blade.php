<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Richiesta cancellazione account</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #e74c3c; color: #fff; padding: 18px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 24px; border-radius: 0 0 10px 10px; }
        .btn { display: inline-block; padding: 12px 22px; background: #e74c3c; color: #fff !important; text-decoration: none; border-radius: 8px; margin-top: 12px; }
        .footer { text-align: center; margin-top: 16px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0;font-size:1.2rem;">Richiesta di cancellazione account</h1>
    </div>
    <div class="content">
        <p>L'utente ha comunicato la <strong>sua intenzione di cancellare il proprio account</strong> da <strong>{{ config('app.name', 'Excursio') }}</strong>.</p>
        <ul>
            <li><strong>Username (nickname):</strong> {{ $user->username }}</li>
            <li><strong>Nome:</strong> {{ trim((string) $user->nome) !== '' ? $user->nome : '—' }}</li>
            <li><strong>Cognome:</strong> {{ trim((string) $user->cognome) !== '' ? $user->cognome : '—' }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>ID utente:</strong> {{ $user->getKey() }}</li>
        </ul>
        <p style="margin-bottom:8px;">Accedi alla gestione utenti per procedere con l'eliminazione definitiva dell'account utilizzando le funzionalità amministrative già disponibili.</p>
        <p>
            <a class="btn" href="{{ $usersAdminUrl }}">Apri gestione utenti</a>
        </p>
    </div>
    <div class="footer">
        Notifica automatica — {{ config('app.name', 'Excursio') }}
    </div>
</body>
</html>