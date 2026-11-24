<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function __invoke(Order $order): View
    {
        return view('kasir.receipt', ['order' => $order->load(['items', 'meja', 'kasir'])]);
    }
}
