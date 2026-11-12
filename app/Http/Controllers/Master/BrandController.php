<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Brand::withCount('products');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $brands = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('master.brands.index', compact('brands', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::record('create_brand', 'Master Data', "Menambahkan merek/brand: {$brand->name}");
        return back()->with('success', "Merek {$brand->name} berhasil ditambahkan!");
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name,' . $brand->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $brand->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::record('update_brand', 'Master Data', "Memperbarui merek/brand: {$brand->name}");
        return back()->with('success', "Merek {$brand->name} berhasil diperbarui!");
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', "Merek {$brand->name} tidak dapat dihapus karena masih digunakan oleh produk.");
        }

        $brand->delete();
        ActivityLog::record('delete_brand', 'Master Data', "Menghapus merek/brand: {$brand->name}");
        return back()->with('success', "Merek {$brand->name} berhasil dihapus.");
    }
}

