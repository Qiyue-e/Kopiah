@extends('layouts.app')
@section('title', 'Akses ditolak')
@section('content')
<section class="max-w-xl mx-auto px-4 py-24 text-center">
  <div class="text-6xl">🔒</div>
  <h1 class="font-serif text-4xl mt-5">akses ditolak</h1>
  <p class="text-sm text-coffee/65 mt-3">{{ $exception->getMessage() ?: 'kamu tidak punya izin untuk membuka halaman ini.' }}</p>
  <a class="btn btn-coffee mt-6" href="{{ route('home') }}">Kembali ke beranda</a>
</section>
@endsection
