<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Supplier::withCount('purchases');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();
        return view('master.suppliers.index', compact('suppliers', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'contact_name' => $validated['contact_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('create_supplier', 'Mitra', "Menambahkan supplier baru: {$supplier->name}");
        return back()->with('success', "Supplier {$supplier->name} berhasil ditambahkan!");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchases' => fn($q) => $q->latest()->take(10),
            'payables',
            'payments' => fn($q) => $q->latest()->take(10),
        ]);

        $totalPurchases = (float) $supplier->purchases()->where('status', 'received')->sum('total_amount');
        $totalPayables = (float) $supplier->payables()->where('remaining_balance', '>', 0)->sum('remaining_balance');

        return view('master.suppliers.show', compact('supplier', 'totalPurchases', 'totalPayables'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:suppliers,code,' . $supplier->id],
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'contact_name' => $validated['contact_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::record('update_supplier', 'Mitra', "Memperbarui supplier: {$supplier->name}");
        return back()->with('success', "Supplier {$supplier->name} berhasil diperbarui!");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->count() > 0) {
            return back()->with('error', "Supplier {$supplier->name} memiliki riwayat transaksi pembelian sehingga tidak dapat dihapus.");
        }

        $supplier->delete();
        ActivityLog::record('delete_supplier', 'Mitra', "Menghapus supplier: {$supplier->name}");
        return back()->with('success', "Supplier {$supplier->name} berhasil dihapus.");
    }
}

