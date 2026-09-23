{{--
    Iconos SVG del portal del alumno.

    El tema trae Font Awesome 6 Pro pero el CSS del panel redefine las clases
    `fa*` contra una familia "Font Awesome 5" y varias webfonts responden 404,
    así que los iconos del portal salían como cuadros vacíos. Estos SVG no
    dependen de ninguna fuente.

    Uso: @include('customers.includes.icon', ['name' => 'play'])
         Opcional: 'size' (px, por defecto hereda 1em) y 'stroke' (grosor).
--}}
@php
    $paths = [
        'cap' => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/>',
        'play' => '<path d="M6 4.5v15l13-7.5Z" fill="currentColor" stroke="none"/>',
        'play-circle' => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5v7l6-3.5Z"/>',
        'chart' => '<path d="M3 17l5-6 4 4 5-7 4 5"/>',
        'award' => '<circle cx="12" cy="9" r="6"/><path d="m8.2 14.3-1.4 7 5.2-2.6 5.2 2.6-1.4-7"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'download' => '<path d="M12 3v12"/><path d="m7 11 5 5 5-5"/><path d="M4 20h16"/>',
        'warning' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
        'refresh' => '<path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.06-6.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.22 4.55"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/>',
        'receipt' => '<path d="M4 3h13l3 3v15l-3-1.5L14 21l-3-1.5L8 21l-3-1.5L4 21Z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'folder' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2.5h6a2 2 0 0 1 2 2V17a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
        'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'home' => '<path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
        'gear' => '<circle cx="12" cy="12" r="3.2"/><path d="M19.9 13.6a1.7 1.7 0 0 0 .4 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-2.9-1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0-1.2-2.9H3.6a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 2.9-1.2V3.6a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 2.9 1.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
        'arrow-right' => '<path d="m9 18 6-6-6-6"/>',
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'shield-check' => '<path d="M12 3 4 6v6c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'book' => '<path d="M4 5h16v12H4z"/><path d="M8 21h8"/>',
        'credit-card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'expand' => '<path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'dots' => '<circle cx="5" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1.4" fill="currentColor" stroke="none"/>',
        // ── Iconos del aula / evaluaciones ──────────────────────────────
        'circle-play' => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5v7l6-3.5Z"/>',
        'video' => '<path d="M4 6h11a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/><path d="m16 10 5-3v10l-5-3"/>',
        'text' => '<path d="M4 5h16M4 10h16M4 15h10"/>',
        'audio' => '<path d="M4 9v6h4l5 4V5L8 9Z"/><path d="M17 8a5 5 0 0 1 0 8"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="m4 18 5-5 4 4 3-3 4 4"/>',
        'pdf' => '<path d="M6 3h8l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M13 3v5h5"/>',
        'zip' => '<path d="M6 3h12a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M11 3v2M13 5v2M11 7v2M13 9v2"/>',
        'file' => '<path d="M6 3h8l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M13 3v5h5"/>',
        'quiz' => '<path d="M12 3 21 8v8l-9 5-9-5V8Z"/><path d="m9 12 2 2 4-4"/>',
        'list-check' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="m3 6 1.5 1.5L7 5M3 12l1.5 1.5L7 11M3 18l1.5 1.5L7 17"/>',
        'chart' => '<path d="M3 17l5-6 4 4 5-7 4 5"/>',
        'chart-simple' => '<path d="M5 20V10M12 20V4M19 20v-7"/>',
        'layers' => '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'circle-check' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
        'circle-x' => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',
        'circle-question' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.8.4-1 .9-1 1.7"/><path d="M12 17h.01"/>',
        'circle-alert' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/>',
        'flag' => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'grad' => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/><path d="M22 10v5"/>',
        'user-grad' => '<path d="M12 3 3 7l9 4 9-4-9-4Z"/><path d="M7 9v4c0 1.5 2.5 3 5 3s5-1.5 5-3V9"/>',
        'star' => '<path d="m12 3 2.6 5.6L21 9.3l-4.5 4.3 1.1 6.4L12 17l-5.6 3 1.1-6.4L3 9.3l6.4-.7Z"/>',
        'send' => '<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>',
        'spinner' => '<path d="M12 3a9 9 0 1 0 9 9"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'book' => '<path d="M4 5h16v12H4z"/><path d="M8 21h8"/>',
        'bolt' => '<path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z" fill="currentColor" stroke="none"/>',
        'whatsapp' => '<path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z"/><path d="M8.5 8.3c.2-.5.5-.5.7-.5h.5c.2 0 .4 0 .6.4.2.5.7 1.6.7 1.7.1.1.1.3 0 .4-.1.2-.2.3-.3.4l-.4.5c-.1.1-.2.3-.1.5.2.3.7 1.1 1.5 1.8.9.8 1.6 1.1 1.9 1.2.2.1.4.1.5-.1l.5-.6c.2-.2.3-.2.5-.1l1.5.7c.2.1.4.2.4.3.1.2.1.9-.2 1.3-.3.5-1.2.9-1.7.9-.5 0-1.1 0-3.3-1.4-2.2-1.4-3.2-3.7-3.3-3.9-.1-.2-.9-1.2-.9-2.3 0-1.1.6-1.6.8-1.8Z" fill="currentColor" stroke="none"/>',
    ];

    $d = $paths[$name] ?? '';
    $sz = isset($size) ? $size.'px' : '1em';
    $sw = $stroke ?? 2;
@endphp
@if($d)
    <svg class="icon-svg" viewBox="0 0 24 24" width="{{ $sz }}" height="{{ $sz }}" fill="none" stroke="currentColor"
         stroke-width="{{ $sw }}" stroke-linecap="round" stroke-linejoin="round"
         aria-hidden="true" focusable="false">{!! $d !!}</svg>
@endif
