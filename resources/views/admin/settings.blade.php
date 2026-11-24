@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('content')
<section class="card p-5 sm:p-7 max-w-4xl">
  <h2 class="font-serif text-2xl">Identitas dan operasional</h2>
  <p class="text-sm text-coffee/60 mt-2">informasi ini digunakan di website pelanggan dan checkout.</p>
  <form method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="grid sm:grid-cols-2 gap-4 mt-5">
    @csrf @method('PUT')
    <div><label class="field-label">Nama cafe</label><input class="field" name="cafe_name" required value="{{ old('cafe_name', $settings['cafe_name']) }}"></div>
    <div><label class="field-label">Tagline</label><input class="field" name="tagline" value="{{ old('tagline', $settings['tagline']) }}"></div>
    <div><label class="field-label">Jam buka</label><input class="field" name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours']) }}"></div>
    <div><label class="field-label">Nomor WhatsApp (kode negara)</label><input class="field" name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp']) }}" placeholder="6281234567890"></div>
    <div><label class="field-label">Instagram</label><input class="field" name="instagram" value="{{ old('instagram', $settings['instagram']) }}" placeholder="@kopiah.id"></div>
    <div><label class="field-label">Alamat cafe</label><input class="field" name="address" value="{{ old('address', $settings['address']) }}"></div>
    <div><label class="field-label">Pajak (%)</label><input class="field" type="number" step="0.01" min="0" max="100" name="tax_percent" value="{{ old('tax_percent', $settings['tax_percent']) }}"></div>
    <div><label class="field-label">Service charge (%)</label><input class="field" type="number" step="0.01" min="0" max="100" name="service_percent" value="{{ old('service_percent', $settings['service_percent']) }}"></div>
    <div class="sm:col-span-2"><label class="field-label">Informasi pembayaran QRIS</label><textarea class="field" rows="2" name="qris_info">{{ old('qris_info', $settings['qris_info']) }}</textarea>
      <p class="text-xs text-coffee/50 mt-1">aplikasi ini mencatat status QRIS “menunggu” dan tetap memerlukan konfirmasi kasir, bukan payment gateway otomatis.</p></div>
    <div class="sm:col-span-2"><label class="field-label">URL Google Maps embed</label><input class="field" name="google_maps_embed" value="{{ old('google_maps_embed', $settings['google_maps_embed']) }}"><p class="text-xs text-coffee/50 mt-1">gunakan URL embed dari Google Maps, bukan link halaman biasa.</p></div>
    <div class="sm:col-span-2">
      <label class="field-label">Logo (opsional)</label>
      <input class="field" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
      <p class="text-xs text-coffee/50 mt-1">maksimal 2 MB. unggah logo baru untuk mengganti logo tersimpan.</p>
      <img src="{{ site_logo() }}" alt="Logo saat ini" class="w-20 h-20 rounded-xl object-contain mt-3 bg-white p-2">
    </div>
    <div class="sm:col-span-2"><button class="btn btn-coffee">Simpan pengaturan</button></div>
  </form>
</section>
<section id="logs" class="card p-5 mt-5">
  <div class="flex justify-between items-center"><h2 class="font-serif text-2xl">Log aktivitas</h2><span class="text-xs text-coffee/50">50 aktivitas terbaru</span></div>
  <div class="table-wrap mt-4"><table class="admin-table">
    <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Detail</th></tr></thead>
    <tbody>
      @forelse ($logs as $log)
        <tr><td class="whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td><td>{{ $log->user->nama ?? 'Sistem' }}</td><td>{{ $log->aksi }}</td><td>{{ $log->detail }}</td></tr>
      @empty
        <tr><td colspan="4">belum ada aktivitas.</td></tr>
      @endforelse
    </tbody>
  </table></div>
</section>
@endsection
