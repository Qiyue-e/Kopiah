<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:12', 'confirmed', 'different:current_password'],
        ]);

        $request->user()->update(['password' => $data['password']]); // di-hash oleh cast `hashed`
        $request->session()->regenerate();

        ActivityLog::record('change_password', 'Pengguna mengganti password sendiri');

        return redirect()->route('staff.password.edit')->with('success', 'password berhasil diganti.');
    }
}
