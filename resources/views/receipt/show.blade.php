@extends('layouts.app')
@section('title', 'Struk '.$order->kode_pesanan)
@section('content')
<section class="max-w-3xl mx-auto px-4 py-10">
  @include('receipt._viewer', ['order' => $order, 'backUrl' => route('track.show', ['kode' => $order->kode_pesanan]), 'backLabel' => 'Lacak pesanan'])
</section>
@endsection
