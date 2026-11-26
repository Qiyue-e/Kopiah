@props(['variant' => 'line', 'lines' => 1])
{{-- Placeholder berbentuk konten (shimmer hanya transform). Pakai: <x-skeleton class="h-4 w-40" /> atau variant="card"|"block"|"circle"|"text". --}}
@if ($variant === 'text')
  <div {{ $attributes->merge(['class' => 'sk-stack']) }} role="status" aria-label="Memuat">
    @for ($i = 0; $i < $lines; $i++)<span class="skeleton sk-line" @if ($i === $lines - 1 && $lines > 1) style="width:60%" @endif></span>@endfor
  </div>
@elseif ($variant === 'card')
  <div {{ $attributes->merge(['class' => 'card sk-card']) }} role="status" aria-label="Memuat">
    <span class="skeleton sk-block" style="aspect-ratio:4/3"></span>
    <div class="p-4"><span class="skeleton sk-line" style="width:40%"></span><span class="skeleton sk-line mt-3" style="height:16px;width:75%"></span><span class="skeleton sk-line mt-3"></span></div>
  </div>
@else
  <span {{ $attributes->merge(['class' => 'skeleton '.($variant === 'circle' ? 'sk-circle' : ($variant === 'block' ? 'sk-block' : 'sk-line'))]) }} role="status" aria-label="Memuat"></span>
@endif
