@props(['name', 'filled' => false])
{{-- Ikon garis, satu set (gaya Lucide), 16-18px, stroke 1.5. Warna mengikuti teks (currentColor). --}}
@php
  $paths = [
    'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'map-pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'message-circle' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
    'qr-code' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3z"/><path d="M21 14v3"/><path d="M14 21h3"/><path d="M21 21h-1"/>',
    'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"/><circle cx="12" cy="13" r="3"/>',
    'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
    'shopping-bag' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
    'star' => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>',
    'copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
  ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon']) }} width="16" height="16" viewBox="0 0 24 24" fill="{{ $filled ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
