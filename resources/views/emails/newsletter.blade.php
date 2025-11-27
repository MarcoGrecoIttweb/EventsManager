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
            background: linear-gradient(45deg, #6c5ce7, #a29bfe);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 10px 10px;
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
            background: #6c5ce7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>EventSite Newsletter</h1>
</div>

<div class="content">
    <h2>Ciao {{ $user->name }}!</h2>

    <div>
        {!! nl2br(e($content)) !!}
    </div>

    <hr>

    <p>
        <strong>Non rispondere a questa email.</strong><br>
        Questa è una comunicazione automatica da EventSite.
    </p>

    <a href="{{ url('/') }}" class="button">Visita il nostro sito</a>
</div>

<div class="footer">
    <p>&copy; {{ date('Y') }} EventSite. Tutti i diritti riservati.</p>
    <p>
        <a href="{{ url('/') }}">EventSite</a> |
        <a href="{{ url('/privacy') }}">Privacy Policy</a> |
        <a href="{{ url('/unsubscribe') }}">Cancellati dalla newsletter</a>
    </p>
</div>
</body>
</html>
