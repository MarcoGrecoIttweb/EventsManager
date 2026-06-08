<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .meta-box {
            background: #e9f5ee;
            border-left: 4px solid #198754;
            padding: 12px 16px;
            margin: 0;
            font-size: 0.92rem;
            color: #155724;
        }
        .meta-box a {
            color: #198754;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 10px 10px;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 0.8em;
        }
        .greeting {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1em;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #198754;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Comunicazione relativa all'evento: {{ $eventTitle }}</h1>
</div>

<div class="meta-box">
    <strong>Evento:</strong> {{ $eventTitle }}<br>
    <strong>Quando:</strong> {{ $eventWhen }}<br>
    <strong>Link:</strong> <a href="{{ $eventUrl }}">{{ $eventUrl }}</a>
</div>

<div class="content">
    <p class="greeting">Ciao {{ $recipientName }},</p>

    <div>
        {!! $body !!}
    </div>

    <hr>

    <a href="{{ $eventUrl }}" class="button">Vai all'evento</a>
</div>

<div class="footer">
    <p>&copy; {{ date('Y') }} Excursio. Tutti i diritti riservati.</p>
    <p><a href="{{ url('/') }}">Excursio</a></p>
</div>

</body>
</html>
