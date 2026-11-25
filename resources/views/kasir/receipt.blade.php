@extends('layouts.admin')
@section('title', 'Struk '.$order->kode_pesanan)
@section('content')
@include('receipt._viewer', ['order' => $order, 'backUrl' => route('kasir.orders.index')])
@endsection
