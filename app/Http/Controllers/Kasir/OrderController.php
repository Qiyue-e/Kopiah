<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('kasir.orders.index', [
            'menus' => Menu::orderBy('nama')->get(['id', 'nama', 'stok', 'is_available']),
        ]);
    }

    /** Potongan HTML daftar pesanan; di-refresh halaman setiap 5 detik. */
    public function list(OrderPaymentService $payments): View
    {
        $payments->expireDue(); // pesanan QRIS terabaikan dibersihkan walau scheduler belum dijalankan

        $orders = Order::with(['items', 'meja', 'kasir'])
            ->visibleToKasir()
            ->notCancelled()
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()->limit(80)->get();

        return view('kasir.orders._cards', ['orders' => $orders]);
    }

    /**
     * Dipolling semua halaman kasir (public/js/kasir-notify.js) untuk notifikasi pesanan baru.
     * - ids: semua pesanan sah dalam 24 jam terakhir (pembanding sisi browser untuk mendeteksi yang baru)
     * - orders: ringkasan untuk toast; pesanan POS (dibuat kasir sendiri) tidak ikut karena bukan "pesanan masuk"
     * - new_count: badge, yaitu pesanan sah yang masih berstatus Diterima (belum diproses)
     */
    public function poll(OrderPaymentService $payments): JsonResponse
    {
        $payments->expireDue();

        $window = fn () => Order::visibleToKasir()->notCancelled()->where('created_at', '>=', now()->subDay());

        $recent = $window()->with(['items', 'meja'])->orderByDesc('id')->limit(40)->get();
        $today = Order::visibleToKasir()->notCancelled()->whereDate('created_at', today())->get(['id', 'status_pesanan']);

        return response()->json([
            'ids' => $recent->pluck('id')->values(),
            'open' => $today->whereIn('status_pesanan', ['Diterima', 'Diproses', 'Siap'])->count(),
            'new_count' => $window()->where('status_pesanan', 'Diterima')->count(),
            'orders' => $recent->whereNull('kasir_id')->map(fn (Order $o) => [
                'id' => $o->id,
                'antrean' => $o->antrean_label,
                'kode' => $o->kode_pesanan,
                'nama' => $o->nama_pelanggan,
                'meja' => $o->meja ? 'Meja '.$o->meja->nomor_meja : 'Take away',
                'total' => (int) round($o->total),
                'lunas' => $o->status_bayar === 'Lunas',
                'metode' => $o->metode_bayar,
                'items' => $o->items->map(fn ($i) => ['nama' => $i->nama_menu, 'qty' => $i->qty])->values(),
            ])->values(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['status_pesanan' => ['required', Rule::in(Order::STATUSES)]]);

        $order->update($data + ['kasir_id' => $order->kasir_id ?? $request->user()->id]);
        ActivityLog::record('update_order_status', "Pesanan {$order->kode_pesanan} → {$data['status_pesanan']}");

        return $this->respond($request, 'status pesanan diperbarui.');
    }

    public function updatePayment(Request $request, Order $order, OrderPaymentService $payments): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status_bayar' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
            'metode_bayar' => ['required', Rule::in(Order::METHODS)],
        ]);

        // Konfirmasi manual oleh kasir juga harus memberi nomor antrean dan waktu bayar (idempoten).
        if ($data['status_bayar'] === 'Lunas' && $order->status_bayar !== 'Lunas') {
            $payments->markPaid($order);
            $order->refresh();
        }

        $order->update($data + ['kasir_id' => $order->kasir_id ?? $request->user()->id]);
        ActivityLog::record('update_order_payment', "Pesanan {$order->kode_pesanan} → {$data['status_bayar']} ({$data['metode_bayar']})");

        return $this->respond($request, 'pembayaran diperbarui.');
    }

    public function toggleMenu(Menu $menu): RedirectResponse
    {
        $menu->update(['is_available' => ! $menu->is_available]);
        ActivityLog::record('toggle_menu', 'Kasir mengubah ketersediaan menu ID '.$menu->id);

        return back()->with('success', 'ketersediaan menu diperbarui.');
    }

    private function respond(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? response()->json(['ok' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
