<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const KEYS = [
        'cafe_name', 'tagline', 'opening_hours', 'address', 'whatsapp', 'instagram',
        'tax_percent', 'service_percent', 'qris_info', 'google_maps_embed', 'logo_path',
    ];

    public function edit(): View
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => (string) Setting::read($key, '')])->all();

        return view('admin.settings', [
            'settings' => $settings,
            'logs' => ActivityLog::with('user')->latest()->limit(50)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cafe_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'opening_hours' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'instagram' => ['nullable', 'string', 'max:60'],
            'tax_percent' => ['required', 'numeric', 'between:0,100'],
            'service_percent' => ['required', 'numeric', 'between:0,100'],
            'qris_info' => ['nullable', 'string', 'max:500'],
            'google_maps_embed' => ['nullable', 'url', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], ['tax_percent.between' => 'pajak dan service charge harus di antara 0–100%.', 'service_percent.between' => 'pajak dan service charge harus di antara 0–100%.']);

        $values = collect($data)->except('logo')->map(fn ($v) => (string) ($v ?? ''))->all();
        if ($request->hasFile('logo')) {
            $values['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        Setting::write($values);
        ActivityLog::record('update_settings', 'Pengaturan cafe diperbarui');

        return redirect()->route('admin.settings.edit')->with('success', 'pengaturan berhasil disimpan.');
    }
}
