<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Moment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Setujui / tolak foto Moments — boleh dilakukan owner maupun kasir. */
class MomentModerationController extends Controller
{
    public function update(Request $request, Moment $moment): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]]);

        $moment->update($data);
        ActivityLog::record('moderate_moment', $data['status'].' ID '.$moment->id);

        return back()->with('success', 'status foto diperbarui.');
    }
}
