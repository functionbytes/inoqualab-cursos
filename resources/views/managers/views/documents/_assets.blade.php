@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/documents/designs.css') }}?v={{ @filemtime(public_path('managers/css/views/documents/designs.css')) ?: '1' }}">
@endpush
@push('scripts')
<script src="{{ asset('managers/js/views/documents/designs.js') }}?v={{ @filemtime(public_path('managers/js/views/documents/designs.js')) ?: '1' }}"></script>
@endpush
