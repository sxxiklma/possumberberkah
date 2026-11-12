<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = User::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('system.users.index', compact('users', 'roles', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        ActivityLog::record(
            action: 'create_user',
            module: 'User Management',
            description: "Membuat akun pengguna: {$user->name} dengan role {$validated['role']}"
        );

        return back()->with('success', "Pengguna {$user->name} berhasil ditambahkan!");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->username = strtolower($validated['username']);
        $user->email = strtolower($validated['email']);
        $user->phone = $validated['phone'] ?? null;
        $user->is_active = $request->boolean('is_active', true);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        ActivityLog::record(
            action: 'update_user',
            module: 'User Management',
            description: "Memperbarui akun pengguna: {$user->name}"
        );

        return back()->with('success', "Pengguna {$user->name} berhasil diperbarui!");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record(
            action: 'delete_user',
            module: 'User Management',
            description: "Menghapus akun pengguna: {$name}"
        );

        return back()->with('success', "Pengguna {$name} berhasil dihapus.");
    }
}

