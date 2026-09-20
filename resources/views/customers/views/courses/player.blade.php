<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('customers/css/views/courses/player.css') }}">
</head>
<body>
    <div class="player-wrap">
        <iframe
            src="{{ $embedUrl }}"
            title="Reproductor de video de la lección"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
            allowfullscreen
            referrerpolicy="strict-origin-when-cross-origin"
        ></iframe>

        <div class="shield">
            <div class="s-top"></div>
            <div class="s-share"></div>
            <div class="s-more"></div>
            <div class="s-logo"></div>
        </div>
    </div>
</body>
</html>
