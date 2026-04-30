<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuovo annuncio Mercatino</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background: #198754; color: #fff; padding: 18px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 24px; border-radius: 0 0 10px 10px; }
        .btn { display: inline-block; padding: 12px 22px; background: #0d6efd; color: #fff !important; text-decoration: none; border-radius: 8px; margin-top: 12px; }
        .muted { color: #666; font-size: 12px; }
        .card { background:#fff; border:1px solid #dee2e6; border-radius:10px; padding:14px 16px; }
        ul { margin-top: 0.25rem; }
    </style>
</head>
<body>
<div class="header">
    <h1 style="margin:0;font-size:1.2rem;">Nuovo annuncio inserito nel Mercatino</h1>
</div>
<div class="content">
    <p>
        Un utente ha inviato una nuova bozza annuncio su <strong>{{ config('app.name', 'Excursio') }}</strong>.
    </p>

    <div class="card">
        <p style="margin-top:0;"><strong>Dettagli annuncio</strong></p>
        <ul>
            <li><strong>Titolo:</strong> {{ $annuncio['titolo'] ?? '—' }}</li>
            <li><strong>Categoria:</strong> {{ $annuncio['categoria'] ?? '—' }}</li>
            <li><strong>Prezzo:</strong> {{ $annuncio['tipo_prezzo'] ?? '—' }}@if(($annuncio['tipo_prezzo'] ?? '') === 'fisso' && isset($annuncio['prezzo'])) — {{ number_format((float) $annuncio['prezzo'], 2, ',', '.') }} €@endif</li>
            <li><strong>Condizione:</strong> {{ $annuncio['condizione'] ?? '—' }}</li>
            <li><strong>Zona ritiro:</strong> {{ $annuncio['zona_ritiro'] ?? '—' }}</li>
            <li><strong>Contatto preferito:</strong> {{ $annuncio['contatto'] ?? '—' }}</li>
            <li><strong>Foto caricate:</strong> {{ (int) ($annuncio['foto_caricate'] ?? 0) }}</li>
            <li><strong>Inviato il:</strong> {{ $annuncio['inviato_il'] ?? '—' }}</li>
        </ul>
        <p style="margin-bottom:0;">
            <strong>Descrizione:</strong><br>
            <span style="white-space: pre-wrap;">{{ $annuncio['descrizione'] ?? '' }}</span>
        </p>
    </div>

    <div class="card" style="margin-top:14px;">
        <p style="margin-top:0;"><strong>Autore</strong></p>
        <ul style="margin-bottom:0;">
            <li><strong>Username:</strong> {{ $autore['username'] ?? '—' }}</li>
            <li><strong>ID utente:</strong> {{ $autore['id'] ?? '—' }}</li>
            <li><strong>Email:</strong> {{ $autore['email'] ?? '—' }}</li>
        </ul>
    </div>

    <p style="margin-bottom:0;">
        <a class="btn" href="{{ $mercatinoUrl }}">Apri Mercatino</a>
    </p>

    <p class="muted" style="margin-top:14px;">
        Notifica automatica — {{ config('app.name', 'Excursio') }}
    </p>
</div>
</body>
</html>

