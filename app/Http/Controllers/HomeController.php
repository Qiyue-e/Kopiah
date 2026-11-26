<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Moment;
use App\Models\Promo;
use App\Models\Review;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'featured' => Menu::with('category')->available()->orderByDesc('is_best_seller')->latest()->limit(4)->get(),
            'promos' => Promo::available()->limit(3)->get(),
            'reviews' => Review::approved()->latest()->limit(3)->get(),
            'moments' => Moment::approved()->latest()->limit(12)->get()->filter->hasFile()->take(4)->values(), // lewati foto yang filenya hilang
        ]);
    }

    public function about(): View
    {
        return view('about');
    }
}
