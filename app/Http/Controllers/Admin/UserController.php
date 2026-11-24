<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Pengelolaan akun kasir oleh owner (akun owner tidak bisa dibuat/diubah dari sini). */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users', ['users' => User::kasir()->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'regex:/^[a-zA-Z0-9_.-]{3,50}$/', 'unique:users,username'],
            'password' => ['required', 'string', 'min:10'],
        ], ['username.regex' => 'username 3–50 karakter (huruf, angka, titik, garis bawah, atau minus).']);

        $user = User::create($data + ['role' => 'kasir', 'is_active' => true]);
        ActivityLog::record('create_cashier', $user->username);

        return back()->with('success', 'akun kasir dibuat.');
    }

    public function toggle(User $user): RedirectResponse
    {
        abort_unless($user->role === 'kasir', 404);

        $user->update(['is_active' => ! $user->is_active]);
        ActivityLog::record('toggle_cashier', 'ID '.$user->id);

        return back()->with('success', 'status akun kasir diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'kasir', 404);

        $data = $request->validate(['password' => ['required', 'string', 'min:10']]);

        $user->update(['password' => $data['password']]);
        ActivityLog::record('reset_cashier_password', 'ID '.$user->id);

        return back()->with('success', 'password berhasil direset. berikan password baru secara aman.');
    }
}
