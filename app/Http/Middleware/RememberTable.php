<?php

namespace App\Http\Middleware;

use App\Models\DiningTable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Mengingat meja yang dipindai dari QR (?token=...) agar otomatis terpilih saat checkout. */
class RememberTable
{
    public function handle(Request $request, Closure $next): Response
    {
        $table = null;

        if ($token = $request->query('token')) {
            $table = DiningTable::where('qr_token', (string) $token)->first();
        } elseif (ctype_digit((string) $request->query('meja'))) {
            $table = DiningTable::find((int) $request->query('meja'));
        }

        if ($table) {
            $request->session()->put('selected_table_id', $table->id);
        }

        return $next($request);
    }
}
