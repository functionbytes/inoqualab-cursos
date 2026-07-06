@php
    $user = $certificate->user;
    $course = $certificate->course;
    $certifier = $certificate->certifier;

    $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    $courseTitle = $course['title'] ?? '';
    $duration = $course['duration'] ?? null;
    $hours = $duration ? ($duration == 1 ? '1 hora' : $duration . ' horas') : null;
    $code = strtoupper($certificate->slack);
    $issued = certificate_date($certificate->start_at ?? $certificate->end_at);
    $certifierName = trim(($certifier->firstname ?? '') . ' ' . ($certifier->lastname ?? ''));
    $certifierRole = strip_tags($certifier->description ?? 'Dirección académica');
    // Marca de agua: primeras letras del curso
    $wm = strtoupper(\Illuminate\Support\Str::of($courseTitle)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(3)->implode(''));
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
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --navy:#0d1b2a; --navy2:#16304a; --blue:#008bcd; --blued:#006fa3; --gold:#c9a227; --ink:#1b2a3a; --muted:#6a7888; }
        body { font-family:'Plus Jakarta Sans',system-ui,sans-serif; background:#e9eef3; color:var(--ink); -webkit-font-smoothing:antialiased; }
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
        .cert { width:1040px; max-width:100%; aspect-ratio:1.414/1; background:#fff; position:relative; box-shadow:0 30px 80px rgba(13,27,42,.22); overflow:hidden; }
        .cert-inner { position:absolute; inset:26px; border:2px solid var(--blue); }
        .cert-inner::before { content:''; position:absolute; inset:7px; border:1px solid rgba(13,27,42,.18); }
        .cert-corner { position:absolute; width:64px; height:64px; border:0 solid var(--navy); }
        .cc1 { top:18px; left:18px; border-top-width:5px; border-left-width:5px; border-color:var(--blue); }
        .cc2 { top:18px; right:18px; border-top-width:5px; border-right-width:5px; border-color:var(--blue); }
        .cc3 { bottom:18px; left:18px; border-bottom-width:5px; border-left-width:5px; border-color:var(--blue); }
        .cc4 { bottom:18px; right:18px; border-bottom-width:5px; border-right-width:5px; border-color:var(--blue); }
        .cert-band { position:absolute; top:0; left:0; right:0; height:8px; background:linear-gradient(90deg,var(--navy),var(--blue)); }
        .cert-wm { position:absolute; bottom:-40px; right:-30px; font-family:'Playfair Display',serif; font-size:300px; font-weight:800; color:rgba(13,27,42,.03); line-height:1; pointer-events:none; }
        .cert-content { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; text-align:center; padding:64px 80px 56px; }
        .cert-logo { font-size:24px; font-weight:800; letter-spacing:-.5px; color:var(--navy); }
        .cert-logo b { color:var(--blue); }
        .cert-logo .dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--blue); margin-left:3px; }
        .cert-sub { font-size:10.5px; font-weight:700; letter-spacing:.28em; text-transform:uppercase; color:var(--blued); margin-top:6px; }
        .cert-h1 { font-family:'Playfair Display',serif; font-size:42px; font-weight:800; color:var(--navy); letter-spacing:.01em; margin-top:26px; }
        .cert-rule { width:70px; height:3px; border-radius:2px; background:var(--blue); margin:14px 0 22px; }
        .cert-label { font-size:13px; color:var(--muted); font-weight:500; letter-spacing:.02em; }
        .cert-name { font-family:'Playfair Display',serif; font-size:48px; font-weight:700; color:var(--navy); margin:10px 0 6px; text-transform:capitalize; }
        .cert-name-line { width:380px; max-width:70%; height:1.5px; background:rgba(13,27,42,.2); margin-bottom:22px; }
        .cert-body { font-size:15px; color:#3c4d5e; line-height:1.7; max-width:640px; }
        .cert-course { font-size:21px; font-weight:800; color:var(--navy); margin:10px 0; letter-spacing:-.01em; }
        .cert-meta { display:flex; gap:26px; margin-top:6px; color:var(--muted); font-size:13px; font-weight:600; }
        .cert-meta b { color:var(--ink); }
        .cert-foot { position:absolute; left:80px; right:80px; bottom:56px; display:flex; align-items:flex-end; justify-content:space-between; }
        .cert-sign { text-align:center; width:220px; }
        .cert-sign .sig { font-family:'Playfair Display',serif; font-size:24px; color:var(--navy); }
        .cert-sign .ln { height:1.5px; background:rgba(13,27,42,.25); margin:4px 0 8px; }
        .cert-sign .nm { font-size:12.5px; font-weight:800; color:var(--ink); text-transform:capitalize; }
        .cert-sign .rl { font-size:11px; color:var(--muted); font-weight:600; margin-top:2px; }
        .cert-seal { width:108px; height:108px; position:relative; flex:0 0 auto; }
        .cert-seal .ring { position:absolute; inset:0; border-radius:50%; background:radial-gradient(circle at 50% 40%, var(--blue), var(--navy)); display:flex; align-items:center; justify-content:center; box-shadow:0 8px 22px rgba(13,27,42,.3); }
        .cert-seal .ring svg { width:46px; height:46px; color:#fff; }
        .cert-seal .ribbon { position:absolute; bottom:-12px; left:50%; transform:translateX(-50%); display:flex; gap:5px; }
        .cert-seal .ribbon span { width:14px; height:26px; background:var(--blue); clip-path:polygon(0 0,100% 0,100% 100%,50% 78%,0 100%); }
        .cert-seal .ribbon span:last-child { background:var(--navy); }
        .cert-verify { position:absolute; left:80px; bottom:22px; font-size:10.5px; color:var(--muted); font-weight:600; letter-spacing:.02em; }
        .cert-verify b { color:var(--ink); font-family:ui-monospace,monospace; }
        .cert-date { position:absolute; right:80px; bottom:22px; font-size:10.5px; color:var(--muted); font-weight:600; }
        @media (max-width:880px) {
            .cert-content { padding:40px 44px; }
            .cert-h1 { font-size:30px; } .cert-name { font-size:34px; } .cert-course { font-size:17px; }
            .cert-foot { left:44px; right:44px; bottom:40px; }
            .cert-sign { width:140px; } .cert-verify,.cert-date { left:44px; right:auto; bottom:14px; } .cert-date{left:auto;right:44px;}
            .cert-wm { font-size:180px; }
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
            <div class="cert-band"></div>
            <div class="cert-inner"></div>
            <span class="cert-corner cc1"></span><span class="cert-corner cc2"></span><span class="cert-corner cc3"></span><span class="cert-corner cc4"></span>
            <div class="cert-wm">{{ $wm ?: 'INO' }}</div>

            <div class="cert-content">
                <div class="cert-logo">INOQUA<b>LAB</b><span class="dot"></span></div>
                <div class="cert-sub">Centro de capacitación en inocuidad</div>

                <div class="cert-h1">Certificado de finalización</div>
                <div class="cert-rule"></div>

                <div class="cert-label">Se otorga el presente certificado a</div>
                <div class="cert-name">{{ \Illuminate\Support\Str::lower($fullName) }}</div>
                <div class="cert-name-line"></div>

                <div class="cert-body">
                    Por haber completado satisfactoriamente el curso
                    <div class="cert-course">{{ $courseTitle }}</div>
                    cumpliendo con todos los módulos, evaluaciones y requisitos establecidos.
                </div>
                <div class="cert-meta">
                    @if($hours)<span>Intensidad: <b>{{ $hours }}</b></span>@endif
                    <span>Calificación: <b>Aprobado</b></span>
                </div>
            </div>

            <div class="cert-foot">
                <div class="cert-sign">
                    <div class="sig">{{ \Illuminate\Support\Str::of($certifierName ?: 'INOQUALAB')->explode(' ')->first() }}</div>
                    <div class="ln"></div>
                    <div class="nm">{{ \Illuminate\Support\Str::lower($certifierName ?: 'INOQUALAB') }}</div>
                    <div class="rl">{{ $certifierRole }}</div>
                </div>
                <div class="cert-seal">
                    <div class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="6"/><path d="M8.5 14 7 22l5-3 5 3-1.5-8"/></svg></div>
                    <div class="ribbon"><span></span><span></span></div>
                </div>
                <div class="cert-sign">
                    <div class="sig">INOQUALAB</div>
                    <div class="ln"></div>
                    <div class="nm">Coordinación de certificación</div>
                    <div class="rl">Bucaramanga, Colombia</div>
                </div>
            </div>

            <div class="cert-verify">Código de verificación: <b>{{ $code }}</b></div>
            <div class="cert-date">Expedido el {{ $issued }}</div>
        </div>
    </div>

</body>
</html>
