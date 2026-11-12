<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $query = Customer::withCount('sales');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        return view('master.customers.index', compact('customers', 'search', 'category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'in:umum,retail,grosir,reseller,pelanggan_tetap'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'category' => $validated['category'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('create_customer', 'Mitra', "Menambahkan pelanggan baru: {$customer->name}");

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return back()->with('success', "Pelanggan {$customer->name} berhasil ditambahkan!");
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'sales' => fn($q) => $q->latest()->take(10),
            'receivables',
            'payments' => fn($q) => $q->latest()->take(10),
        ]);

        $totalSales = (float) $customer->sales()->where('status', 'completed')->sum('total_amount');
        $totalReceivables = (float) $customer->receivables()->where('remaining_balance', '>', 0)->sum('remaining_balance');

        return view('master.customers.show', compact('customer', 'totalSales', 'totalReceivables'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:customers,code,' . $customer->id],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'in:umum,retail,grosir,reseller,pelanggan_tetap'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'category' => $validated['category'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::record('update_customer', 'Mitra', "Memperbarui pelanggan: {$customer->name}");
        return back()->with('success', "Pelanggan {$customer->name} berhasil diperbarui!");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->count() > 0) {
            return back()->with('error', "Pelanggan {$customer->name} memiliki riwayat transaksi penjualan sehingga tidak dapat dihapus.");
        }

        $customer->delete();
        ActivityLog::record('delete_customer', 'Mitra', "Menghapus pelanggan: {$customer->name}");
        return back()->with('success', "Pelanggan {$customer->name} berhasil dihapus.");
    }
}

