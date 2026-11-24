<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function show(Request $request): View
    {
        $code = strtoupper(trim((string) $request->query('kode', '')));
        $order = $code !== '' ? Order::with(['meja', 'review'])->where('kode_pesanan', $code)->first() : null;

        return view('track', ['code' => $code, 'order' => $order]);
    }

    public function review(Request $request, Order $order): RedirectResponse
    {
        if ($order->status_pesanan !== 'Selesai') {
            return back()->withErrors(['ulasan' => 'ulasan baru bisa dikirim setelah pesanan selesai.']);
        }

        if ($order->review()->exists()) {
            return back()->withErrors(['ulasan' => 'ulasan untuk pesanan ini sudah pernah dikirim.']);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->review()->create($data + ['status' => 'pending']);

        return redirect()->route('track.show', ['kode' => $order->kode_pesanan, 'review' => 1]);
    }

    /** Dipanggil halaman lacak setiap 5 detik. */
    public function status(Order $order): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'order' => $order->only(['kode_pesanan', 'status_pesanan', 'status_bayar', 'metode_bayar', 'total']),
        ]);
    }
}
