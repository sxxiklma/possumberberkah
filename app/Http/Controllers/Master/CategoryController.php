<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Category::withCount('products');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
        }

        $categories = $query->latest()->paginate(15)->withQueryString();
        return view('master.categories.index', compact('categories', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create([
            'code' => $validated['code'] ? strtoupper($validated['code']) : ('CAT-' . strtoupper(Str::random(4))),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('create_category', 'Master Data', "Menambahkan kategori: {$category->name}");
        return back()->with('success', "Kategori {$category->name} berhasil ditambahkan!");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:categories,code,' . $category->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'code' => $validated['code'] ? strtoupper($validated['code']) : $category->code,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::record('update_category', 'Master Data', "Memperbarui kategori: {$category->name}");
        return back()->with('success', "Kategori {$category->name} berhasil diperbarui!");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', "Kategori {$category->name} tidak dapat dihapus karena masih digunakan oleh produk.");
        }

        $category->delete();
        ActivityLog::record('delete_category', 'Master Data', "Menghapus kategori: {$category->name}");
        return back()->with('success', "Kategori {$category->name} berhasil dihapus.");
    }
}

