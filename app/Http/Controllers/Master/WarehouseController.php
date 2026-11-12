<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with(['locations'])->withCount('batches')->get();
        return view('master.warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ]);

        $wh = Warehouse::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('create_warehouse', 'Master Data', "Menambahkan gudang: {$wh->name}");
        return back()->with('success', "Gudang {$wh->name} berhasil ditambahkan!");
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code,' . $warehouse->id],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $warehouse->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::record('update_warehouse', 'Master Data', "Memperbarui gudang: {$warehouse->name}");
        return back()->with('success', "Gudang {$warehouse->name} berhasil diperbarui!");
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouse_locations,code'],
            'area' => ['nullable', 'string', 'max:100'],
            'rack' => ['nullable', 'string', 'max:50'],
            'row' => ['nullable', 'string', 'max:50'],
            'column' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $loc = WarehouseLocation::create($validated);
        ActivityLog::record('create_warehouse_location', 'Master Data', "Menambahkan lokasi rak gudang: {$loc->code}");

        return back()->with('success', "Lokasi rak {$loc->code} berhasil ditambahkan!");
    }

    public function destroyLocation(WarehouseLocation $location)
    {
        if ($location->products()->count() > 0) {
            return back()->with('error', "Lokasi rak {$location->code} tidak dapat dihapus karena ditautkan dengan produk.");
        }

        $location->delete();
        return back()->with('success', "Lokasi rak {$location->code} berhasil dihapus.");
    }
}

