<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Unit::withCount('products');

        if ($search) {
            $query->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
        }

        $units = $query->paginate(15)->withQueryString();
        return view('master.units.index', compact('units', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:units,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $unit = Unit::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::record('create_unit', 'Master Data', "Menambahkan satuan: {$unit->code}");
        return back()->with('success', "Satuan {$unit->code} berhasil ditambahkan!");
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:units,code,' . $unit->id],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $unit->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::record('update_unit', 'Master Data', "Memperbarui satuan: {$unit->code}");
        return back()->with('success', "Satuan {$unit->code} berhasil diperbarui!");
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', "Satuan {$unit->code} tidak dapat dihapus karena masih digunakan sebagai satuan dasar produk.");
        }

        $unit->delete();
        ActivityLog::record('delete_unit', 'Master Data', "Menghapus satuan: {$unit->code}");
        return back()->with('success', "Satuan {$unit->code} berhasil dihapus.");
    }
}

