@extends('layouts.admin')
@section('title', 'Moderasi Review')
@section('content')
<div class="flex flex-wrap gap-2 mb-5 no-print">
  @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $key => $label)
    <a class="px-4 py-2 rounded-full text-sm {{ $filter === $key ? 'bg-coffee text-white' : 'bg-white border border-coffee/10' }}" href="{{ route('admin.moderation.index', ['filter' => $key]) }}">{{ $label }}</a>
  @endforeach
</div>

<section class="card p-5">
  <div class="flex justify-between items-center"><div><h2 class="font-serif text-2xl">Review pelanggan</h2><p class="text-xs text-coffee/55 mt-1">kelola persetujuan ulasan pelanggan. sistem moderasi review tetap berjalan.</p></div><span class="status-pill">{{ $reviews->count() }} ulasan</span></div>
  <div class="table-wrap mt-4"><table class="admin-table">
    <thead><tr><th>Pelanggan</th><th>Rating</th><th>Komentar</th><th>Pesanan</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse ($reviews as $review)
        <tr>
          <td>{{ $review->nama }}</td>
          <td><span class="rating">{{ str_repeat('★', $review->rating) }}</span></td>
          <td>{{ $review->komentar }}</td>
          <td>{{ $review->order->kode_pesanan ?? '-' }}</td>
          <td><span class="status-pill {{ $review->status === 'approved' ? 'status-green' : ($review->status === 'rejected' ? 'status-red' : 'status-orange') }}">{{ $review->status }}</span></td>
          <td>
            <form method="post" action="{{ route('admin.reviews.moderate', $review) }}" class="flex gap-2">
              @csrf @method('PATCH')
              @if ($review->status !== 'approved')
                <button class="text-xs underline" name="status" value="approved">Setujui</button>
                <button class="text-xs underline text-red-700" name="status" value="rejected">Tolak</button>
              @else
                <button class="text-xs underline text-red-700" name="status" value="rejected">Tarik</button>
              @endif
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6">belum ada review pada filter ini.</td></tr>
      @endforelse
    </tbody>
  </table></div>
</section>
@endsection
