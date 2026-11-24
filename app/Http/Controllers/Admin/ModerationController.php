<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Moderasi review pelanggan. Kopiah Moments tidak lagi memakai persetujuan. */
class ModerationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = in_array($request->query('filter'), ['pending', 'approved', 'rejected', 'all'], true)
            ? $request->query('filter')
            : 'pending';

        $byStatus = fn ($q) => $q->when($filter !== 'all', fn ($x) => $x->where('status', $filter));

        return view('admin.moderation', [
            'filter' => $filter,
            'reviews' => $byStatus(Review::with('order'))->latest()->limit(100)->get(),
        ]);
    }

    public function review(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]]);

        $review->update($data);
        ActivityLog::record('moderate_review', $data['status'].' ID '.$review->id);

        return back()->with('success', 'status moderasi diperbarui.');
    }
}
