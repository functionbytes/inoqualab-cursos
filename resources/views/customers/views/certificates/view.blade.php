@php
    // Mismo diseño que customers.views.certificates.download (el PDF real):
    // plantilla-imagen oficial (certification->thumbnail) con el texto
    // superpuesto en posiciones absolutas calibradas contra esa imagen. Antes
    // esta vista tenía un diseño distinto e inventado que nunca coincidía con
    // el PDF descargado.
    $user = $certificate->user;
    $course = $certificate->course;
    $certification = $certificate->certification;
    $certifier = $certificate->certifier;

    $thumbnail = $certification?->getFirstMedia('thumbnail');
    $signatureMedia = $certifier?->getFirstMedia('signature');

    $image = $thumbnail ? '/media/'.$thumbnail->id.'/'.$thumbnail->file_name : '';
    $signature = $signatureMedia ? '/media/'.$signatureMedia->id.'/'.$signatureMedia->file_name : '';

    $start = certificate_date($certificate->start_at);
    $end = certificate_date($certificate->end_at);

    $fullName = trim(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
    // D5: mismo escalado que el PDF para que el nombre no desborde el certificado
    $nameSize = strlen($fullName) > 28 ? '21px' : (strlen($fullName) > 20 ? '24px' : '30px');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex,nofollow">
    <title>Certificado · {{ $fullName }} · INOQUALAB</title>
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --navy:#0d1b2a; --blue:#008bcd; --ink:#1b2a3a; }
        body { font-family:'Montserrat',system-ui,sans-serif; background:#e9eef3; color:var(--ink); -webkit-font-smoothing:antialiased; }

        .cert-bar { position:sticky; top:0; z-index:10; background:var(--navy); color:#fff; display:flex; align-items:center; justify-content:space-between; padding:14px 28px; }
        .cert-bar .l { display:flex; align-items:center; gap:14px; }
        .cert-bar a.back { display:inline-flex; align-items:center; gap:8px; color:rgba(255,255,255,.8); text-decoration:none; font-size:13.5px; font-weight:700; padding:8px 12px; border-radius:9px; transition:background .15s; }
        .cert-bar a.back:hover { background:rgba(255,255,255,.1); color:#fff; }
        .cert-bar a.back svg { width:16px; height:16px; }
        .cert-bar .ttl { font-size:14px; font-weight:800; letter-spacing:.04em; }
        .cert-bar .actions { display:flex; gap:10px; }
        .cert-bar button, .cert-bar a.btnx { display:inline-flex; align-items:center; gap:9px; border:none; border-radius:10px; padding:11px 20px; font-size:13.5px; font-weight:800; cursor:pointer; font-family:inherit; text-decoration:none; transition:transform .15s, filter .15s, background .15s; }
        .cert-bar .dl { background:var(--blue); color:#fff; box-shadow:0 8px 20px rgba(0,139,205,.35); }
        .cert-bar .dl:hover { transform:translateY(-2px); filter:brightness(1.07); }
        .cert-bar .dl svg { width:16px; height:16px; }
        .cert-bar .ghost { background:rgba(255,255,255,.1); color:#fff; }
        .cert-bar .ghost:hover { background:rgba(255,255,255,.2); }

        .cert-stage { padding:36px 20px 60px; display:flex; justify-content:center; }
        /* Aspect ratio A4 landscape, igual que setPaper('a4','landscape') en el PDF */
        .cert { width:1040px; max-width:100%; aspect-ratio:1.4142/1; background:#fff; position:relative; box-shadow:0 30px 80px rgba(13,27,42,.22); overflow:hidden; }
        .cert .bg { position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; display:block; }

        /* Mismas clases/posiciones (%) que download.blade.php, calibradas contra
           la plantilla-imagen -- escaladas ~0.926 (1040/1123px, ancho aprox. de
           una página A4 landscape a 96dpi) para que el texto coincida en tamaño
           relativo con el del PDF. */
        /* Posiciones re-calibradas a ojo contra la plantilla-imagen real
           (public/media/194/1681965223default.jpg) en vez de copiar los % de
           download.blade.php: ese CSS usa line-height:0 (+ en varios casos
           bottom:0;margin:auto) para anclar el texto -- un truco que dompdf
           interpreta distinto a un navegador real, y aquí producía texto
           desplazado/solapado con las líneas fijas de la imagen. En su lugar:
           line-height normal + transform:translateY(-50%) centra cada bloque
           de texto exactamente en su coordenada "top", de forma predecible. */
        .cert p { text-align:center; }
        .text-time { color:#1a1a16; font-size:17px; font-weight:600; text-transform:uppercase; line-height:1.2; }
        .text-certifier { color:#1a1a16; font-size:11px; font-weight:600; text-transform:uppercase; line-height:1.2; }
        .text-certifier-description { color:#575756; font-size:9px; font-weight:400; line-height:1.3; }
        .text-user { color:#1a1a16; font-weight:600; text-transform:uppercase; line-height:1.2; }
        .text-identification { color:#1a1a16; font-size:19px; font-weight:600; text-transform:uppercase; line-height:1.2; }
        .text-course { color:#1a1a16; font-size:17px; font-weight:600; text-transform:uppercase; line-height:1.2; }
        .text-end, .text-start { color:#1a1a16; font-size:17px; font-weight:600; text-transform:uppercase; line-height:1.2; }

        .certifier { position:absolute; top:89%; left:66%; width:34%; text-align:center; transform:translateY(-50%); }
        .certifier-description { position:absolute; top:93%; left:66%; width:34%; text-align:center; transform:translateY(-50%); }
        .signature { position:absolute; top:82%; left:80%; width:93px; transform:translateY(-50%); }
        .user { position:absolute; top:41%; left:0; width:100%; text-align:center; transform:translateY(-50%); }
        .identification { position:absolute; top:49.7%; left:51.08%; text-align:center; transform:translateY(-50%); }
        .start { position:absolute; top:68.5%; left:40.08%; text-align:center; transform:translateY(-50%); }
        .end { position:absolute; top:68.5%; left:69%; text-align:center; transform:translateY(-50%); }
        .time { position:absolute; top:62.8%; left:59%; text-align:center; transform:translateY(-50%); }
        .course { position:absolute; top:59.6%; left:0; width:100%; text-align:center; transform:translateY(-50%); }

        @media (max-width:880px) {
            .text-time, .text-course, .text-end, .text-start { font-size:13px; }
            .text-identification { font-size:15px; }
            .text-certifier { font-size:9px; }
            .text-certifier-description { font-size:7.5px; }
            .signature { width:70px; }
        }
        @media print {
            @page { size: A4 landscape; margin: 0; }
            body { background:#fff; }
            .cert-bar { display:none; }
            .cert-stage { padding:0; }
            .cert { width:100%; height:100vh; aspect-ratio:auto; box-shadow:none; }
        }
    </style>
</head>
<body>

    <div class="cert-bar">
        <div class="l">
            <a class="back" href="{{ route('customers.certificates') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> Volver a certificados</a>
            <span class="ttl">Certificado de finalización</span>
        </div>
        <div class="actions">
            <button class="ghost" onclick="window.print()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg> Imprimir</button>
            <a class="dl btnx" href="{{ route('customers.certificate.download', $certificate->slack) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg> Descargar PDF</a>
        </div>
    </div>

    <div class="cert-stage">
        <div class="cert">
            @if($image)
                <img class="bg" src="{{ $image }}" alt="">
            @endif

            <div class="user">
                <p class="text-user" style="font-size:{{ $nameSize }};">{{ $fullName }}</p>
            </div>

            <div class="identification">
                <p class="text-identification">{{ $user['identification'] }}</p>
            </div>

            <div class="course">
                <p class="text-course">{{ $course['title'] }}</p>
            </div>

            <div class="time">
                @if($course['duration'] == 1)
                    <p class="text-time">{{ $course['duration'] }} hora.</p>
                @else
                    <p class="text-time">{{ $course['duration'] }} horas.</p>
                @endif
            </div>

            <div class="start">
                <p class="text-start">{{ $start }}</p>
            </div>

            <div class="end">
                <p class="text-end">{{ $end }}</p>
            </div>

            @if($signature)
                <img class="signature" src="{{ $signature }}" alt="">
            @endif

            @if($certifier)
                <div class="certifier">
                    <p class="text-certifier">{{ $certifier->firstname ?? '' }} {{ $certifier->lastname ?? '' }}</p>
                </div>
                <div class="certifier-description">
                    <p class="text-certifier-description">{{ strip_tags($certifier->description ?? '') }}</p>
                </div>
            @endif
        </div>
    </div>

</body>
</html>
