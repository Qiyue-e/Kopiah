@extends('layouts.admin')
@section('title', 'Kopiah Moments')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
  <div>
    <p class="text-sm text-coffee/60">kelola foto yang dibagikan pelanggan Kopiah.</p>
    <p class="text-xs text-coffee/50 mt-1">foto baru langsung tampil di feed. hanya owner yang dapat menghapus Moments pelanggan.</p>
  </div>
  <a class="btn btn-light" href="{{ route('moments.index') }}" target="_blank">Lihat feed pelanggan ↗</a>
</div>

<div class="flex flex-wrap gap-2 mb-5">
  @foreach (['all' => 'Semua', 'approved' => 'Tayang', 'rejected' => 'Ditolak'] as $key => $label)
    <a class="px-4 py-2 rounded-full text-sm {{ $filter === $key ? 'bg-coffee text-white' : 'bg-white border border-coffee/10' }}" href="{{ route('admin.moments.index', ['filter' => $key]) }}">{{ $label }}</a>
  @endforeach
</div>

<section class="card p-5">
  <div class="flex justify-between items-center gap-3">
    <div><h2 class="font-serif text-2xl">Foto Moments</h2><p class="text-xs text-coffee/55 mt-1">hapus foto yang tidak sesuai. penghapusan juga menghapus reaksi terkait.</p></div>
    <span class="status-pill">{{ $moments->total() }} foto</span>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-5">
    @forelse ($moments as $moment)
      <article class="border border-coffee/10 bg-white rounded-2xl overflow-hidden">
        <img src="{{ $moment->url }}" class="w-full aspect-square object-cover" alt="Foto Moments dari {{ $moment->nama_pelanggan ?: 'pelanggan' }}" loading="lazy">
        <div class="p-3">
          <div class="flex items-center justify-between gap-2">
            <b class="text-sm truncate">{{ $moment->avatar ?: '☕' }} {{ $moment->nama_pelanggan ?: 'Tanpa nama' }}</b>
            <span class="status-pill {{ $moment->status === 'approved' ? 'status-green' : ($moment->status === 'rejected' ? 'status-red' : 'status-orange') }}">{{ $moment->status }}</span>
          </div>
          @if ($moment->caption)<p class="text-xs text-coffee/65 mt-2 leading-5">{{ $moment->caption }}</p>@endif
          <p class="text-[10px] text-coffee/40 mt-2">{{ $moment->tipe_foto }} · {{ $moment->created_at->format('Y-m-d H:i') }} · {{ $moment->reactions_count }} reaksi</p>
          <form method="post" action="{{ route('admin.moments.destroy', $moment) }}" class="mt-3" onsubmit="return confirm('hapus Moments ini secara permanen? file foto dan seluruh reaksinya akan ikut dihapus.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-light !py-2 !px-3 !text-xs text-red-700 border-red-200 w-full">Hapus Moments</button>
          </form>
        </div>
      </article>
    @empty
      <p class="text-sm text-coffee/55">belum ada foto pada filter ini.</p>
    @endforelse
  </div>

  @if ($moments->hasPages())
    <div class="mt-6">{{ $moments->links() }}</div>
  @endif
</section>
@endsection
