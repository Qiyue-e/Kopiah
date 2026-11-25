<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.xendit.secret_key' => 'xnd_development_test',
            'services.xendit.webhook_token' => 'token-rahasia',
            'services.xendit.expiry_minutes' => 10,
        ]);

        $category = Category::create(['nama' => 'Kopi']);
        $this->menu = Menu::create([
            'category_id' => $category->id, 'nama' => 'Kopi Susu', 'harga' => 20000, 'stok' => 10, 'is_available' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function cart(int $qty = 2): array
    {
        return ['cart' => ['k' => ['key' => 'k', 'menu_id' => $this->menu->id, 'option_ids' => [], 'qty' => $qty, 'note' => '']]];
    }

    private function checkoutQris(int $qty = 2)
    {
        return $this->withSession($this->cart($qty))
            ->postJson(route('checkout.store'), ['nama_pelanggan' => 'Rani', 'metode_bayar' => 'QRIS']);
    }

    private function fakeXendit(string $id = 'qr_abc'): void
    {
        Http::fake(['api.xendit.co/qr_codes' => Http::response([
            'id' => $id, 'status' => 'ACTIVE', 'qr_string' => '00020101021226-contoh-qris-'.$id,
        ], 201)]);
    }

    private function webhook(Order $order, array $override = [], string $token = 'token-rahasia')
    {
        return $this->postJson(route('webhooks.xendit'), [
            'event' => 'qr.payment',
            'data' => array_merge([
                'id' => 'qrpy_1', 'qr_id' => $order->xendit_qr_id, 'reference_id' => $order->kode_pesanan,
                'amount' => (int) round($order->total), 'status' => 'SUCCEEDED',
            ], $override),
        ], ['x-callback-token' => $token]);
    }

    public function test_checkout_qris_creates_pending_order_that_is_hidden_from_kasir_and_has_no_queue_number(): void
    {
        $res = $this->checkoutQris()->assertOk()->assertJsonPath('amount', 40000)->assertJsonPath('ok', true);

        $order = Order::firstOrFail();
        $this->assertSame('Menunggu QRIS', $order->status_bayar);
        $this->assertNull($order->nomor_antrean);
        $this->assertNotNull($order->expires_at);
        $this->assertEqualsWithDelta(600, now()->diffInSeconds($order->expires_at, false), 5);
        $this->assertSame(8, $this->menu->fresh()->stok);
        $this->assertSame(0, Order::visibleToKasir()->count());
        $this->assertStringContainsString($order->token, $res->json('pay_url'));
    }

    public function test_qris_is_created_with_exact_order_total_and_secret_key_stays_on_server(): void
    {
        $this->fakeXendit();
        $this->checkoutQris(3);
        $order = Order::firstOrFail();

        $this->postJson(route('payment.start', $order->token))
            ->assertOk()->assertJsonPath('amount', 60000)->assertJsonPath('qr_string', '00020101021226-contoh-qris-qr_abc');

        Http::assertSent(function ($request) use ($order) {
            return $request->url() === 'https://api.xendit.co/qr_codes'
                && $request['amount'] === 60000
                && $request['type'] === 'DYNAMIC'
                && $request['currency'] === 'IDR'
                && $request['reference_id'] === $order->kode_pesanan
                && $request->hasHeader('api-version', '2022-07-31')
                && $request->hasHeader('Authorization');
        });

        // Memanggil ulang (refresh / "Coba lagi") tidak membuat QR kedua.
        $this->postJson(route('payment.start', $order->token))->assertOk();
        Http::assertSentCount(1);

        $this->assertStringNotContainsString('xnd_development_test', $this->get(route('payment.show', $order->token))->getContent());
    }

    public function test_gateway_failure_returns_retryable_error_and_retry_works(): void
    {
        Http::fake(['api.xendit.co/qr_codes' => Http::sequence()
            ->push(['error_code' => 'SERVER_ERROR'], 500)
            ->push(['id' => 'qr_ok', 'qr_string' => 'QRSTRING'], 201)]);
        $this->checkoutQris();
        $order = Order::firstOrFail();

        $this->postJson(route('payment.start', $order->token))->assertStatus(502)->assertJsonPath('retryable', true);
        $this->postJson(route('payment.start', $order->token))->assertOk()->assertJsonPath('qr_string', 'QRSTRING');
    }

    public function test_webhook_rejects_missing_or_wrong_token(): void
    {
        $this->fakeXendit();
        $this->checkoutQris();
        $order = Order::firstOrFail();
        $this->postJson(route('payment.start', $order->token));
        $order->refresh();

        $this->webhook($order, [], 'salah')->assertStatus(401);
        $this->postJson(route('webhooks.xendit'), ['data' => ['status' => 'SUCCEEDED']])->assertStatus(401);
        $this->assertSame('Menunggu QRIS', $order->fresh()->status_bayar);

        config(['services.xendit.webhook_token' => '']);
        $this->webhook($order, [], '')->assertStatus(401); // fail closed jika token belum dikonfigurasi
    }

    public function test_webhook_marks_paid_once_assigns_queue_number_and_is_idempotent(): void
    {
        $this->fakeXendit();
        $this->checkoutQris();
        $order = Order::firstOrFail();
        $this->postJson(route('payment.start', $order->token));
        $order->refresh();

        $this->getJson(route('payment.status', $order->token))->assertJsonPath('state', 'pending');

        $this->webhook($order)->assertOk()->assertJsonPath('processed', true);
        $paid = $order->fresh();
        $this->assertSame('Lunas', $paid->status_bayar);
        $this->assertSame(1, $paid->nomor_antrean);
        $this->assertSame('001', $paid->antrean_label);
        $this->assertSame('qrpy_1', $paid->xendit_payment_id);
        $this->assertSame(1, Order::visibleToKasir()->count());

        // Callback terkirim ulang: tidak diproses lagi, nomor antrean tidak berubah.
        $this->webhook($order)->assertOk()->assertJsonPath('processed', false);
        $this->assertSame(1, $order->fresh()->nomor_antrean);

        $this->getJson(route('payment.status', $order->token))
            ->assertJsonPath('state', 'paid')->assertJsonPath('redirect', route('order.success', $order->token));
    }

    public function test_webhook_ignores_amount_mismatch_unknown_order_and_non_success_status(): void
    {
        $this->fakeXendit();
        $this->checkoutQris();
        $order = Order::firstOrFail();
        $this->postJson(route('payment.start', $order->token));
        $order->refresh();

        $this->webhook($order, ['amount' => 1000])->assertOk()->assertJsonPath('processed', false);
        $this->webhook($order, ['status' => 'FAILED'])->assertOk()->assertJsonPath('processed', false);
        $this->webhook($order, ['reference_id' => 'CFY000000XXXXXX'])->assertOk()->assertJsonPath('processed', false);
        $this->assertSame('Menunggu QRIS', $order->fresh()->status_bayar);
    }

    public function test_queue_numbers_are_sequential_unique_and_reset_each_day_in_jakarta_time(): void
    {
        $this->fakeXendit();
        $payments = app(OrderPaymentService::class);

        // 23:50 WIB tanggal 25 = 16:50 UTC; 00:10 WIB tanggal 26 = 17:10 UTC (sudah hari baru di Jakarta).
        Carbon::setTestNow(Carbon::parse('2026-11-25 23:50:00', 'Asia/Jakarta'));
        $numbers = [];
        foreach (range(1, 3) as $i) {
            $order = $this->makeOrder();
            $payments->markPaid($order);
            $numbers[] = $order->fresh()->nomor_antrean;
        }
        $this->assertSame([1, 2, 3], $numbers);

        Carbon::setTestNow(Carbon::parse('2026-11-26 00:10:00', 'Asia/Jakarta'));
        $next = $this->makeOrder();
        $payments->markPaid($next);
        $this->assertSame(1, $next->fresh()->nomor_antrean);
        $this->assertSame('2026-11-26', $next->fresh()->tanggal_antrean->toDateString());
    }

    public function test_cashier_orders_get_a_queue_number_immediately_and_are_visible(): void
    {
        $this->withSession($this->cart(1))
            ->post(route('checkout.store'), ['nama_pelanggan' => 'Budi', 'metode_bayar' => 'Kasir'])
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(1, $order->nomor_antrean);
        $this->assertSame(1, Order::visibleToKasir()->count());
    }

    public function test_unpaid_order_expires_after_deadline_and_stock_is_restored(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00', 'Asia/Jakarta'));
        $this->fakeXendit();
        $this->checkoutQris(4);
        $order = Order::firstOrFail();
        $this->assertSame(6, $this->menu->fresh()->stok);

        Carbon::setTestNow(Carbon::parse('2026-11-25 10:10:30', 'Asia/Jakarta'));
        $this->getJson(route('payment.status', $order->token))->assertJsonPath('state', 'expired');

        $order->refresh();
        $this->assertSame('Kedaluwarsa', $order->status_bayar);
        $this->assertSame('Dibatalkan', $order->status_pesanan);
        $this->assertNull($order->nomor_antrean);
        $this->assertSame(10, $this->menu->fresh()->stok);

        // Dijalankan lagi (scheduler) tidak mengembalikan stok dua kali.
        $this->assertSame(0, app(OrderPaymentService::class)->expireDue());
        $this->assertSame(10, $this->menu->fresh()->stok);
        $this->postJson(route('payment.start', $order->token))->assertStatus(409)->assertJsonPath('state', 'expired');
    }

    public function test_expire_command_cleans_abandoned_orders(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00', 'Asia/Jakarta'));
        $this->checkoutQris(1);
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:11:00', 'Asia/Jakarta'));

        $this->artisan('orders:expire')->expectsOutput('1 pesanan kedaluwarsa.')->assertSuccessful();
        $this->assertSame('Kedaluwarsa', Order::firstOrFail()->status_bayar);
    }

    public function test_success_and_receipt_pages_only_open_after_payment(): void
    {
        $this->fakeXendit();
        $this->checkoutQris();
        $order = Order::firstOrFail();
        $this->postJson(route('payment.start', $order->token));

        $this->get(route('order.success', $order->token))->assertRedirect(route('payment.show', $order->token));
        $this->get(route('order.receipt', $order->token))->assertRedirect(route('payment.show', $order->token));

        $this->webhook($order->fresh())->assertOk();

        $this->get(route('order.success', $order->token))->assertOk()->assertSee('001')->assertSee('Rani');
        $this->get(route('order.receipt', $order->token))->assertOk()
            ->assertSee('NOMOR ANTREAN')->assertSee('Rp40.000')->assertSee('QRIS')->assertSee($order->kode_pesanan);
        $this->get(route('payment.show', $order->token))->assertRedirect(route('order.success', $order->token));
    }

    public function test_qris_checkout_is_refused_when_gateway_not_configured(): void
    {
        config(['services.xendit.secret_key' => null]);

        $this->checkoutQris()->assertStatus(422);
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $this->menu->fresh()->stok);
    }

    public function test_checkout_page_offers_qris_only_when_gateway_is_configured(): void
    {
        $this->withSession($this->cart())->get(route('checkout.create'))
            ->assertOk()->assertSee('otomatis terkonfirmasi')->assertSee('checkout-pay', false);

        config(['services.xendit.secret_key' => null]);
        $this->withSession($this->cart())->get(route('checkout.create'))
            ->assertOk()->assertSee('belum tersedia')->assertDontSee('checkout-pay', false);
    }

    public function test_kasir_only_sees_paid_orders_and_the_queue_number(): void
    {
        $this->fakeXendit();
        $this->checkoutQris();
        $order = Order::firstOrFail();
        $this->postJson(route('payment.start', $order->token));

        $kasir = \App\Models\User::create(['nama' => 'Kasir', 'username' => 'kasir1', 'password' => 'Rahasia#12345', 'role' => 'kasir', 'is_active' => true]);
        $this->actingAs($kasir);

        // Belum dibayar: tidak terlihat di daftar maupun dashboard kasir, dan tidak memicu notifikasi.
        $this->get(route('kasir.orders.list'))->assertOk()->assertDontSee($order->kode_pesanan);
        $this->get(route('kasir.dashboard'))->assertOk()->assertDontSee($order->kode_pesanan);
        $this->getJson(route('kasir.orders.poll'))->assertJsonPath('ids', []);

        $this->webhook($order->fresh())->assertOk();

        $this->get(route('kasir.orders.list'))->assertOk()->assertSee($order->kode_pesanan)->assertSee('001');
        $this->get(route('kasir.dashboard'))->assertOk()->assertSee('001');
        $this->getJson(route('kasir.orders.poll'))->assertJsonCount(1, 'ids');
        $this->get(route('kasir.receipt', $order->kode_pesanan))->assertOk()->assertSee('NOMOR ANTREAN');
    }

    public function test_cashier_marking_paid_manually_assigns_queue_number_and_paid_time(): void
    {
        $this->checkoutQris();
        $order = Order::firstOrFail();
        $kasir = \App\Models\User::create(['nama' => 'Kasir', 'username' => 'kasir2', 'password' => 'Rahasia#12345', 'role' => 'kasir', 'is_active' => true]);

        $this->actingAs($kasir)
            ->patchJson(route('kasir.orders.payment', $order), ['status_bayar' => 'Lunas', 'metode_bayar' => 'QRIS'])
            ->assertOk();

        $order->refresh();
        $this->assertSame('Lunas', $order->status_bayar);
        $this->assertSame(1, $order->nomor_antrean);
        $this->assertNotNull($order->paid_at);
    }

    public function test_track_page_shows_queue_number_and_receipt_link(): void
    {
        $this->withSession($this->cart(1))->post(route('checkout.store'), ['nama_pelanggan' => 'Budi', 'metode_bayar' => 'Kasir']);
        $order = Order::firstOrFail();

        $this->get(route('track.show', ['kode' => $order->kode_pesanan]))
            ->assertOk()->assertSee('nomor antrean')->assertSee('001')->assertSee(route('order.receipt', $order->token), false);
    }

    private function makeOrder(): Order
    {
        return app(OrderService::class)->place(
            ['nama_pelanggan' => 'Tes', 'metode_bayar' => 'QRIS'],
            collect([['menu_id' => $this->menu->id, 'name' => 'Kopi Susu', 'qty' => 1, 'price' => 20000.0, 'options' => [], 'note' => '']]),
        );
    }
}
