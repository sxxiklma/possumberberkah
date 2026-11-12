<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('system.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        ActivityLog::record(
            action: 'update_settings',
            module: 'Settings',
            description: "Memperbarui konfigurasi sistem toko"
        );

        return back()->with('success', 'Pengaturan toko berhasil diperbarui!');
    }
}

