<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashSession;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Buka/tutup sesi kas harian per kasir. */
class CashSessionController extends Controller
{
    public function index(Request $request): View
    {
        $uid = $request->user()->id;
        $active = CashSession::open()->where('user_id', $uid)->latest('dibuka_pada')->first();

        $sales = 0.0;
        $count = 0;

        if ($active) {
            $row = Order::paid()
                ->where('kasir_id', $uid)
                ->where('metode_bayar', 'Kasir')
                ->where('created_at', '>=', $active->dibuka_pada)
                ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as n')
                ->first();

            $sales = (float) $row->total;
            $count = (int) $row->n;
        }

        return view('kasir.cash', [
            'active' => $active,
            'sales' => $sales,
            'count' => $count,
            'expected' => $active ? $active->modal_awal + $sales : 0,
            'history' => CashSession::where('user_id', $uid)->latest('dibuka_pada')->limit(10)->get(),
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $data = $request->validate(['modal_awal' => ['required', 'numeric', 'min:0']]);

        if (CashSession::open()->where('user_id', $request->user()->id)->exists()) {
            return back()->withErrors(['modal_awal' => 'sesi kas masih terbuka. tutup dulu sebelum membuka sesi baru.']);
        }

        CashSession::create(['user_id' => $request->user()->id, 'modal_awal' => $data['modal_awal'], 'dibuka_pada' => now()]);
        ActivityLog::record('open_cash', 'Modal awal '.rupiah($data['modal_awal']));

        return back()->with('success', 'kasir berhasil dibuka.');
    }

    public function close(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kas_akhir' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $session = CashSession::open()->where('user_id', $request->user()->id)->latest('dibuka_pada')->first();

        if (! $session) {
            return back()->withErrors(['kas_akhir' => 'sesi kas tidak aktif atau bukan milikmu.']);
        }

        $session->update($data + ['ditutup_pada' => now()]);
        ActivityLog::record('close_cash', 'Kas akhir aktual '.rupiah($data['kas_akhir']));

        return back()->with('success', 'kasir berhasil ditutup.');
    }
}
