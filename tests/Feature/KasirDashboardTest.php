<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KasirDashboardTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(string $role): User
    {
        return User::create([
            'nama' => ucfirst($role), 'username' => $role.(++$this->seq), 'password' => 'Rahasia#12345',
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function order(array $attrs = []): Order
    {
        $this->seq++;

        return Order::create($attrs + [
            'kode_pesanan' => 'CFY2611'.str_pad((string) $this->seq, 6, '0', STR_PAD_LEFT),
            'nama_pelanggan' => 'Tamu '.$this->seq,
            'subtotal' => 20000, 'total' => 20000,
            'metode_bayar' => 'QRIS', 'status_bayar' => 'Lunas', 'status_pesanan' => 'Diterima',
        ]);
    }

    private function paidAt(string $jakarta, int $total = 20000, array $extra = []): Order
    {
        $t = Carbon::parse($jakarta, 'Asia/Jakarta');
        $order = $this->order(['paid_at' => $t, 'total' => $total] + $extra);

        $order->forceFill(['created_at' => $t])->save(); // created_at tidak mass-assignable

        return $order;
    }

    /* ---------- B8: ganti password ---------- */

    public function test_kasir_cannot_open_or_submit_change_password(): void
    {
        $kasir = $this->user('kasir');
        $hashBefore = $kasir->password;

        $this->actingAs($kasir)->get(route('staff.password.edit'))->assertForbidden();
        $this->actingAs($kasir)->put(route('staff.password.update'), [
            'current_password' => 'Rahasia#12345', 'password' => 'PasswordBaru#2026', 'password_confirmation' => 'PasswordBaru#2026',
        ])->assertForbidden();

        $this->assertSame($hashBefore, $kasir->fresh()->password);
    }

    public function test_kasir_ui_has_no_change_password_link_but_owner_keeps_it(): void
    {
        $url = route('staff.password.edit');

        $this->actingAs($this->user('kasir'))->get(route('kasir.dashboard'))
            ->assertOk()->assertDontSee($url, false)->assertDontSee('Ganti password');

        $this->actingAs($this->user('owner'))->get(route('admin.dashboard'))
            ->assertOk()->assertSee($url, false)->assertSee('Ganti password');
    }

    public function test_owner_can_still_change_own_password_and_guest_is_sent_to_login(): void
    {
        $this->get(route('staff.password.edit'))->assertRedirect();

        $owner = $this->user('owner');
        $this->actingAs($owner)->get(route('staff.password.edit'))->assertOk();
        $this->actingAs($owner)->put(route('staff.password.update'), [
            'current_password' => 'Rahasia#12345', 'password' => 'PasswordBaru#2026', 'password_confirmation' => 'PasswordBaru#2026',
        ])->assertRedirect(route('staff.password.edit'));

        $this->assertTrue(\Hash::check('PasswordBaru#2026', $owner->fresh()->password));
    }

    public function test_owner_can_still_reset_a_kasir_password(): void
    {
        $kasir = $this->user('kasir');

        $this->actingAs($this->user('owner'))
            ->patch(route('admin.users.reset', $kasir), ['password' => 'ResetOlehOwner#1'])
            ->assertRedirect();

        $this->assertTrue(\Hash::check('ResetOlehOwner#1', $kasir->fresh()->password));
    }

    /* ---------- B6: payload notifikasi ---------- */

    public function test_poll_returns_summary_for_toast_badge_and_skips_pos_orders(): void
    {
        $kasir = $this->user('kasir');
        $web = $this->order(['status_bayar' => 'Lunas', 'nomor_antrean' => 7, 'tanggal_antrean' => now()->toDateString(), 'total' => 48000]);
        $web->items()->create(['menu_id' => null, 'nama_menu' => 'Kopi Aren', 'qty' => 2, 'harga' => 24000, 'opsi_json' => [], 'catatan' => '']);
        $pos = $this->order(['kasir_id' => $kasir->id, 'metode_bayar' => 'Kasir', 'status_bayar' => 'Belum Bayar']);
        $this->order(['status_bayar' => 'Menunggu QRIS']); // QRIS belum dibayar: tidak sah, tidak muncul
        $this->order(['status_pesanan' => 'Selesai']);     // sudah selesai: tidak dihitung di badge

        $json = $this->actingAs($kasir)->getJson(route('kasir.orders.poll'))->assertOk()->json();

        $this->assertContains($web->id, $json['ids']);
        $this->assertContains($pos->id, $json['ids']);
        $this->assertCount(3, $json['ids']);
        $this->assertSame(2, $json['new_count']); // web (Diterima) + POS (Diterima); yang Selesai tidak
        $this->assertSame([$web->id], collect($json['orders'])->where('id', $web->id)->pluck('id')->all());
        $this->assertNotContains($pos->id, collect($json['orders'])->pluck('id')->all());

        $row = collect($json['orders'])->firstWhere('id', $web->id);
        $this->assertSame('007', $row['antrean']);
        $this->assertSame(48000, $row['total']);
        $this->assertTrue($row['lunas']);
        $this->assertSame('Take away', $row['meja']);
        $this->assertSame([['nama' => 'Kopi Aren', 'qty' => 2]], $row['items']);
    }

    public function test_poll_requires_login_and_returns_401_json_for_expired_session(): void
    {
        $this->getJson(route('kasir.orders.poll'))->assertUnauthorized();
    }

    public function test_kasir_layout_loads_notification_script_but_owner_does_not(): void
    {
        $this->actingAs($this->user('kasir'))->get(route('kasir.dashboard'))
            ->assertOk()->assertSee('kasir-notify.js', false)->assertSee('id="kn-root"', false)
            ->assertSee('id="kn-sound"', false)->assertSee('Jam Ramai')->assertDontSee('soundToggle');

        // Owner yang membuka halaman kasir tidak mendapat notifikasi (hanya tag <script> dan kontrol yang diperiksa;
        // nama file juga muncul di komentar skrip dashboard).
        $this->actingAs($this->user('owner'))->get(route('kasir.dashboard'))
            ->assertOk()->assertDontSee('src="'.asset('js/kasir-notify.js').'"', false)->assertDontSee('id="kn-root"', false);
    }

    /* ---------- B7: jam ramai ---------- */

    public function test_peak_hours_groups_paid_orders_by_jakarta_hour_and_marks_peak(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 20:00:00', 'Asia/Jakarta'));
        $this->paidAt('2026-11-25 09:05:00', 20000);
        $this->paidAt('2026-11-25 09:59:59', 30000);
        $this->paidAt('2026-11-25 13:00:00', 50000);
        $this->paidAt('2026-11-25 23:30:00', 10000); // di masa depan tapi masih hari ini: tetap jam 23
        $this->paidAt('2026-11-25 00:00:00', 5000);  // tepat tengah malam WIB = jam 00, hari ini

        $d = $this->actingAs($this->user('kasir'))->getJson(route('kasir.peak-hours'))->assertOk()->json();

        $this->assertCount(24, $d['hours']);
        $this->assertSame(['hour' => 9, 'label' => '09', 'orders' => 2, 'omzet' => 50000], $d['hours'][9]);
        $this->assertSame(1, $d['hours'][13]['orders']);
        $this->assertSame(1, $d['hours'][23]['orders']);
        $this->assertSame(1, $d['hours'][0]['orders']);
        $this->assertSame(5, $d['total_orders']);
        $this->assertSame(115000, $d['total_omzet']);
        $this->assertSame(9, $d['peak']['hour']);
        $this->assertSame('hari-ini', $d['range']);
    }

    public function test_peak_hours_counts_only_paid_not_cancelled_orders(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 20:00:00', 'Asia/Jakarta'));
        $this->paidAt('2026-11-25 10:00:00');
        $this->paidAt('2026-11-25 10:10:00', 20000, ['status_bayar' => 'Menunggu QRIS']);
        $this->paidAt('2026-11-25 10:20:00', 20000, ['status_bayar' => 'Kedaluwarsa', 'status_pesanan' => 'Dibatalkan']);
        $this->paidAt('2026-11-25 10:30:00', 20000, ['status_bayar' => 'Belum Bayar']);
        $this->paidAt('2026-11-25 10:40:00', 20000, ['status_pesanan' => 'Dibatalkan']);

        $d = $this->actingAs($this->user('kasir'))->getJson(route('kasir.peak-hours'))->json();

        $this->assertSame(1, $d['total_orders']);
        $this->assertSame(1, $d['hours'][10]['orders']);
    }

    public function test_peak_hours_ranges_and_validation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 12:00:00', 'Asia/Jakarta'));
        $this->paidAt('2026-11-25 08:00:00');            // hari ini
        $this->paidAt('2026-11-19 08:00:00');            // 6 hari lalu: masuk 7 hari
        $this->paidAt('2026-11-18 23:59:59');            // 7 hari lalu: di luar 7 hari, di dalam 30 hari
        $this->paidAt('2026-10-27 08:00:00');            // 29 hari lalu: masuk 30 hari
        $this->paidAt('2026-10-26 23:59:59');            // 30 hari lalu: di luar

        $kasir = $this->user('kasir');
        $total = fn (string $r) => $this->actingAs($kasir)->getJson(route('kasir.peak-hours', ['rentang' => $r]))->json('total_orders');

        $this->assertSame(1, $total('hari-ini'));
        $this->assertSame(2, $total('7'));
        $this->assertSame(4, $total('30'));
        $this->actingAs($kasir)->getJson(route('kasir.peak-hours', ['rentang' => '90']))->assertStatus(422);
    }

    public function test_peak_hours_tie_breaks_by_omzet_then_earliest_hour_and_empty_has_no_peak(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 20:00:00', 'Asia/Jakarta'));
        $kasir = $this->user('kasir');

        $empty = $this->actingAs($kasir)->getJson(route('kasir.peak-hours'))->json();
        $this->assertNull($empty['peak']);
        $this->assertSame(0, $empty['total_orders']);

        $this->paidAt('2026-11-25 08:00:00', 10000);
        $this->paidAt('2026-11-25 15:00:00', 90000); // sama-sama 1 pesanan, omzet lebih besar menang
        $this->paidAt('2026-11-25 11:00:00', 90000); // omzet sama dengan jam 15, jam lebih awal menang

        $d = $this->actingAs($kasir)->getJson(route('kasir.peak-hours'))->json();
        $this->assertSame(11, $d['peak']['hour']);
    }

    public function test_peak_hours_falls_back_to_created_at_for_legacy_orders_without_paid_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 20:00:00', 'Asia/Jakarta'));
        $legacy = $this->order(['paid_at' => null]);
        $legacy->forceFill(['created_at' => Carbon::parse('2026-11-25 07:15:00', 'Asia/Jakarta')])->save(); // created_at tidak mass-assignable

        $d = $this->actingAs($this->user('kasir'))->getJson(route('kasir.peak-hours'))->json();
        $this->assertSame(1, $d['hours'][7]['orders']);
    }
}
