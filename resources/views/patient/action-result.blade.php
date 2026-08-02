<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Poliklinika Salus</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #F6FAF9;
            color: #1A2629;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border: 1px solid #DCE6E3;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(14, 110, 107, .08);
            max-width: 26rem;
            width: 100%;
            padding: 2rem;
            text-align: center;
        }
        .logo { font-size: 1.4rem; font-weight: 700; color: #0E6E6B; margin-bottom: 1.2rem; }
        .icon { font-size: 2.6rem; margin-bottom: .8rem; }
        h1 { font-size: 1.15rem; margin-bottom: .6rem; }
        p { font-size: .92rem; color: #4A5D62; line-height: 1.55; margin-bottom: .5rem; }
        .detail {
            background: #F0F6F5;
            border-radius: .6rem;
            padding: .8rem;
            margin: 1rem 0;
            font-size: .9rem;
        }
        .phone { display: inline-block; margin-top: .8rem; font-weight: 600; color: #0E6E6B; text-decoration: none; font-size: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Poliklinika Salus</div>
        <div class="icon">{{ $icon }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        @isset($appointment)
            <div class="detail">
                {{ $appointment->service?->name }}<br>
                {{ $appointment->starts_at->format('d.m.Y. u H:i') }} · {{ $appointment->doctor?->full_name }}
            </div>
        @endisset
        <p>Za sva pitanja pozovite nas:</p>
        <a class="phone" href="tel:{{ preg_replace('/\s+/', '', config('clinic.phone')) }}">{{ config('clinic.phone') }}</a>
    </div>
</body>
</html>
