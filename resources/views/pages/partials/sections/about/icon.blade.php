{{-- Íconos de trazo para "Sobre nosotros". Props: $name, $class (opcional). --}}
<svg class="ab-ico {{ $class ?? '' }}" viewBox="0 0 24 24" aria-hidden="true">
@switch($name)
    @case('law')<path d="M12 3v18M5 7h14M7 7l-3 7a3.5 3.5 0 0 0 6 0zM17 7l-3 7a3.5 3.5 0 0 0 6 0zM8 21h8"/>@break
    @case('building')<path d="M4.5 20V6.5a1 1 0 0 1 .5-.87L11 2l6 3.63a1 1 0 0 1 .5.87V20"/><path d="M3 20h16M9 20v-4h4v4M8.5 9.5h1M12.5 9.5h1M8.5 13h1M12.5 13h1"/>@break
    @case('award')<circle cx="12" cy="9" r="6"/><path d="M9 14l-1.5 7L12 19l4.5 2L15 14"/>@break
    @case('doc')<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M10 12h5M10 16h5"/>@break
    @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
    @case('check')<path d="M5 12.5l4.5 4.5L19 7.5"/>@break
    @case('flask')<path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.2 21.5h11.6a2 2 0 0 0 1.7-3L14 9V3"/><path d="M7 15h10"/>@break
    @case('chart')<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>@break
@endswitch
</svg>
