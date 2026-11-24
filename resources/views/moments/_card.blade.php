@php($mine = auth()->guest() && $m->device_token === $device)
@php($counts = $m->reactionCounts())
@php($myReaction = $m->reactionOf($device))
<article class="lk-card" id="moment-{{ $m->id }}" data-id="{{ $m->id }}">
  <div class="lk-extra lk-head">
    <span class="lk-avatar lk-avatar-sm">{{ $m->avatar ?: '☕' }}</span>
    <div class="min-w-0 flex-1">
      <b class="block text-sm truncate">{{ $m->nama_pelanggan ?: 'Teman Kopiah' }}{{ $mine ? ' (kamu)' : '' }}</b>
      <small class="text-xs text-coffee/55">{{ $m->created_at->diffForHumans() }} · {{ $m->tipe_foto === 'Selfie' ? 'selfie' : 'momen' }}</small>
    </div>
    @if ($mine)<button type="button" class="text-xs text-red-700 underline" data-delete>hapus</button>@endif
  </div>
  <div class="lk-photo-wrap">
    <img class="lk-photo" src="{{ $m->url }}" alt="Momen dari {{ $m->nama_pelanggan ?: 'teman Kopiah' }}" loading="lazy">
    @if ($m->caption)<span class="lk-caption">{{ $m->caption }}</span>@endif
  </div>
  <div class="lk-extra lk-reactions" data-reactions>
    @foreach (\App\Models\Moment::REACTIONS as $emoji)
      <button type="button" class="lk-react {{ $myReaction === $emoji ? 'is-on' : '' }}" data-emoji="{{ $emoji }}">
        <span>{{ $emoji }}</span><b>{{ $counts->get($emoji) ?: '' }}</b>
      </button>
    @endforeach
  </div>
</article>
