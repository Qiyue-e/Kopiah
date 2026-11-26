<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Data grafik "Jam Ramai": jumlah pesanan lunas dan omzet per jam (00-23, Asia/Jakarta).
 * Jam diambil dari waktu pembayaran; pesanan lama tanpa paid_at memakai waktu dibuat.
 */
class PeakHoursController extends Controller
{
    /** rentang => jumlah hari (termasuk hari ini). */
    private const RANGES = ['hari-ini' => 1, '7' => 7, '30' => 30];

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['rentang' => ['nullable', Rule::in(array_keys(self::RANGES))]]);
        $range = $data['rentang'] ?? 'hari-ini';

        $from = now(Order::TIMEZONE)->startOfDay()->subDays(self::RANGES[$range] - 1);
        $to = now(Order::TIMEZONE)->endOfDay();

        // Basis data menyimpan waktu dalam zona waktu aplikasi; batas rentang dikonversi ke zona itu.
        $dbFrom = $from->clone()->setTimezone(config('app.timezone'));
        $dbTo = $to->clone()->setTimezone(config('app.timezone'));

        $orders = Order::paid()
            ->where(function ($q) use ($dbFrom, $dbTo) {
                $q->whereBetween('paid_at', [$dbFrom, $dbTo])
                    ->orWhere(fn ($w) => $w->whereNull('paid_at')->whereBetween('created_at', [$dbFrom, $dbTo]));
            })
            ->get(['id', 'total', 'paid_at', 'created_at']);

        $hours = [];
        foreach (range(0, 23) as $h) {
            $hours[$h] = ['hour' => $h, 'label' => sprintf('%02d', $h), 'orders' => 0, 'omzet' => 0];
        }

        foreach ($orders as $order) {
            $h = (int) ($order->paid_at ?? $order->created_at)->clone()->setTimezone(Order::TIMEZONE)->format('G');
            $hours[$h]['orders']++;
            $hours[$h]['omzet'] += (int) round($order->total);
        }

        // Jam tersibuk: pesanan terbanyak; bila sama, omzet lebih besar; bila masih sama, jam lebih awal.
        $peak = null;
        foreach ($hours as $row) {
            if ($row['orders'] === 0) {
                continue;
            }
            if ($peak === null
                || $row['orders'] > $peak['orders']
                || ($row['orders'] === $peak['orders'] && $row['omzet'] > $peak['omzet'])) {
                $peak = $row;
            }
        }

        return response()->json([
            'range' => $range,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'hours' => array_values($hours),
            'total_orders' => $orders->count(),
            'total_omzet' => (int) round($orders->sum('total')),
            'peak' => $peak,
        ]);
    }
}
