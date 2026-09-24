@if (! empty($filterable))
    data-cat="{{ $course->categorie->title ?? '' }}"
    data-price="{{ $card['isFree'] ? 0 : ($card['onSale'] ? $course->discount : $course->price) }}"
    data-discount="{{ $card['onSale'] ? 1 : 0 }}"
    data-level="{{ $course->level ?? '' }}"
    data-rating="{{ $card['rating'] ? floor($card['rating']) : 0 }}"
    data-title="{{ \Illuminate\Support\Str::lower($course->title) }}"
@endif
