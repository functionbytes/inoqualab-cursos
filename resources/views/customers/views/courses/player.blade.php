<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; background: #000; overflow: hidden; }

        .player-wrap {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .player-wrap iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /*
         * Capa padre transparente: pointer-events:none para no bloquear el iframe.
         * Los hijos con pointer-events:all sí interceptan clics en zonas específicas.
         */
        .shield {
            position: absolute;
            inset: 0;
            z-index: 10;
            pointer-events: none;
        }

        /* Barra superior: título del video y canal (clickeables a YouTube) */
        .s-top {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 64px;
            pointer-events: all;
            cursor: default;
        }

        /* Botón compartir — esquina inferior izquierda */
        .s-share {
            position: absolute;
            bottom: 0; left: 0;
            width: 64px;
            height: 50px;
            pointer-events: all;
            cursor: default;
        }

        /* "Más videos" — zona inferior central-derecha */
        .s-more {
            position: absolute;
            bottom: 0; right: 140px;
            width: 160px;
            height: 50px;
            pointer-events: all;
            cursor: default;
        }

        /* Logo YouTube y "Ver en YouTube" — esquina inferior derecha */
        .s-logo {
            position: absolute;
            bottom: 0; right: 0;
            width: 140px;
            height: 50px;
            pointer-events: all;
            cursor: default;
        }
    </style>
</head>
<body>
    <div class="player-wrap">
        <iframe
            src="{{ $embedUrl }}"
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
