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
    // D5: mismo escalado que el PDF para que el nombre no desborde el certificado.
    // En cqw (ver container-type en .cert): escala con el ancho REAL de la
    // caja en vez de un px fijo -- antes, en mobile (donde .cert se achica a
    // ~350px por el max-width:100%), este mismo px quedaba enorme relativo a
    // la caja y el nombre se solapaba con el resto del texto.
    $nameSize = strlen($fullName) > 28 ? '2.019cqw' : (strlen($fullName) > 20 ? '2.5cqw' : '2.885cqw');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex,nofollow">
    <title>Certificado · {{ $fullName }} · INOQUALAB</title>
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('customers/css/views/certificates/view.css') }}">
</head>
<body>

    <div class="cert-bar">
        <div class="l">
            <a class="back" href="{{ route('customers.certificates') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> Volver a certificados</a>
            <span class="ttl">Certificado de finalización</span>
        </div>
        <div class="actions">
            <button class="ghost js-print">Imprimir</button>
            <a class="dl btnx" href="{{ route('customers.certificate.download', $certificate->slack) }}">Descargar</a>
        </div>
    </div>

    <div class="cert-stage">
        <div class="cert">
            @if($image)
                <img class="bg" src="{{ $image }}" alt="">
            @endif

            <div class="user">
                <p class="text-user" style="--name-size: {{ $nameSize }}">{{ $fullName }}</p>
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

    <script src="{{ asset('customers/js/views/certificates/view.js') }}"></script>
</body>
</html>
