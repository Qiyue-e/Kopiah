@extends('layouts.app')
@section('title', 'Halaman tidak ditemukan')
@section('content')
<section class="max-w-xl mx-auto px-4 py-24 text-center">
  <div class="text-6xl">☕</div>
  <h1 class="font-serif text-4xl mt-5">halaman tidak ditemukan</h1>
  <p class="text-sm text-coffee/65 mt-3">sepertinya halaman ini sudah tidak ada atau alamatnya salah.</p>
  <a class="btn btn-coffee mt-6" href="{{ route('menu.index') }}">Lihat menu</a>
</section>
@endsection
