<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\DiningTable;
use App\Models\Promo;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function create(XenditService $xendit): View|RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout', [
            'qrisEnabled' => $xendit->isConfigured(),
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
            'tables' => DiningTable::orderBy('nomor_meja')->get(),
            'selectedTable' => session('selected_table_id'),
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders, XenditService $xendit): RedirectResponse|JsonResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'keranjang kosong.'], 422)
                : redirect()->route('cart.index');
        }

        $data = $request->validated();
        $subtotal = (float) $items->sum('line_total');
        $promo = null;

        if ($data['metode_bayar'] === 'QRIS' && ! $xendit->isConfigured()) {
            return $this->fail($request, 'metode_bayar', 'pembayaran QRIS belum aktif. pilih bayar di kasir.');
        }

        if (filled($data['kode_promo'] ?? null)) {
            $promo = Promo::available()->where('kode', $data['kode_promo'])->first();

            if (! $promo || $subtotal < $promo->min_belanja) {
                return $this->fail($request, 'kode_promo', 'kode promo tidak valid, sudah habis, atau belum memenuhi minimal belanja.');
            }
        }

        try {
            $order = $orders->place($data, $this->toLines($items), $promo);
        } catch (RuntimeException $e) {
            return $this->fail($request, 'checkout', $e->getMessage());
        }

        $this->cart->clear();

        // QRIS: pesanan menunggu pembayaran; QR dibuat dan dipantau di halaman bayar (tanpa pindah ke situs lain).
        if ($order->metode_bayar === 'QRIS') {
            $payUrl = route('payment.show', $order->token);

            return $request->expectsJson()
                ? response()->json([
                    'ok' => true,
                    'pay_url' => $payUrl,
                    'start_url' => route('payment.start', $order->token),
                    'status_url' => route('payment.status', $order->token),
                    'seconds_left' => max(0, (int) now()->diffInSeconds($order->expires_at, false)),
                    'amount' => (int) round($order->total),
                    'kode' => $order->kode_pesanan,
                ])
                : redirect($payUrl);
        }

        return redirect()->route('track.show', ['kode' => $order->kode_pesanan, 'baru' => 1]);
    }

    private function fail(CheckoutRequest $request, string $field, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => [$field => [$message]]], 422);
        }

        return back()->withInput()->withErrors([$field => $message]);
    }

    private function toLines(Collection $items): Collection
    {
        return $items->map(fn ($item) => [
            'menu_id' => $item->menu->id,
            'name' => $item->name,
            'qty' => $item->qty,
            'price' => $item->price,
            'note' => $item->note,
            'options' => $item->options->map(fn ($o) => [
                'id' => $o->id, 'nama' => $o->nama_opsi, 'tipe' => $o->tipe, 'harga' => $o->harga_tambahan,
            ])->all(),
        ]);
    }
}
