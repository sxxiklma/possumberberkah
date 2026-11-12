<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Support login with either email or username
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $attemptData = [
            $loginField => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ];

        if (Auth::attempt($attemptData, $request->boolean('remember'))) {
            $request->session()->regenerate();

            ActivityLog::record(
                action: 'login',
                module: 'Auth',
                description: 'Pengguna ' . Auth::user()->name . ' berhasil login ke sistem.'
            );

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email/Username atau password yang dimasukkan salah, atau akun nonaktif.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::record(
                action: 'logout',
                module: 'Auth',
                description: 'Pengguna ' . Auth::user()->name . ' logout dari sistem.'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    public function profile()
    {
        $maxUploadMb = (int) Setting::get('max_upload_size_mb', 2);
        if ($maxUploadMb <= 0) {
            $maxUploadMb = 2;
        }

        return view('auth.profile', [
            'user' => Auth::user(),
            'maxUploadMb' => $maxUploadMb,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $maxUploadMb = (int) Setting::get('max_upload_size_mb', 2);
        if ($maxUploadMb <= 0) {
            $maxUploadMb = 2;
        }
        $maxKilobytes = $maxUploadMb * 1024;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:' . $maxKilobytes],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'avatar.image' => 'File yang dipilih harus berupa gambar valid.',
            'avatar.mimes' => 'Format file foto yang didukung: JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => "Ukuran foto profil melebihi batas maksimal {$maxUploadMb} MB (dapat disesuaikan di menu Pengaturan).",
        ]);

        $user->name = $validated['name'];
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (array_key_exists('address', $validated)) {
            $user->address = $validated['address'];
        }

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        ActivityLog::record(
            action: 'update_profile',
            module: 'Auth',
            description: 'Pengguna memperbarui profil pribadi dan foto profil.'
        );

        return back()->with('success', 'Profil dan foto berhasil diperbarui!');
    }
}

