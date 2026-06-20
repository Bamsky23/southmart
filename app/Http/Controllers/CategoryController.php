<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Branch;
use App\Helpers\DatabaseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display listing of categories.
     */
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('id')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show form to create a new category.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a new category and replicate to all branch nodes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255',
        ]);

        $slug = $request->slug
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        try {
            DB::transaction(function () use ($request, $slug) {
                // 1. Create on central
                $category = Category::create([
                    'name' => $request->name,
                    'slug' => $slug,
                ]);

                // 2. Replicate to online branch nodes
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    if (DatabaseHelper::isNodeOnline($branch->id)) {
                        $conn = DatabaseHelper::getConnectionName($branch->id);
                        DB::connection($conn)->table('categories')->updateOrInsert(
                            ['id' => $category->id],
                            [
                                'name'       => $category->name,
                                'slug'       => $category->slug,
                                'created_at' => $category->created_at,
                                'updated_at' => $category->updated_at,
                            ]
                        );
                    }
                }

                // 3. Log
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity'    => 'Tambah Kategori',
                    'description' => "Menambahkan kategori baru '{$category->name}' (ID: {$category->id}) dan mereplikasi ke cabang online.",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return redirect()->route('admin.kategori.index')
                ->with('success', 'Kategori berhasil ditambahkan dan direplikasi ke semua cabang online.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit form for a category.
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $productCount = $category->products()->count();
        return view('admin.categories.edit', compact('category', 'productCount'));
    }

    /**
     * Update a category and replicate to all branch nodes.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255',
        ]);

        $slug = $request->slug
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        try {
            DB::transaction(function () use ($request, $category, $slug) {
                $oldName = $category->name;

                // 1. Update on central
                $category->update([
                    'name' => $request->name,
                    'slug' => $slug,
                ]);

                // 2. Replicate to online branch nodes
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    if (DatabaseHelper::isNodeOnline($branch->id)) {
                        $conn = DatabaseHelper::getConnectionName($branch->id);
                        DB::connection($conn)->table('categories')->where('id', $category->id)->update([
                            'name'       => $category->name,
                            'slug'       => $category->slug,
                            'updated_at' => $category->updated_at,
                        ]);
                    }
                }

                // 3. Log
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity'    => 'Edit Kategori',
                    'description' => "Mengubah kategori '{$oldName}' menjadi '{$category->name}' (ID: {$category->id}) dan mereplikasi ke cabang online.",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return redirect()->route('admin.kategori.index')
                ->with('success', 'Kategori berhasil diperbarui dan direplikasi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a category (only if no products are linked).
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Safety check — don't delete if products exist
        $productCount = $category->products()->count();
        if ($productCount > 0) {
            return back()->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih memiliki {$productCount} produk. Pindahkan atau hapus produk terlebih dahulu.");
        }

        try {
            DB::transaction(function () use ($category) {
                $name = $category->name;
                $catId = $category->id;

                // 1. Delete on central
                $category->delete();

                // 2. Replicate delete to online branch nodes
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    if (DatabaseHelper::isNodeOnline($branch->id)) {
                        $conn = DatabaseHelper::getConnectionName($branch->id);
                        DB::connection($conn)->table('categories')->where('id', $catId)->delete();
                    }
                }

                // 3. Log
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity'    => 'Hapus Kategori',
                    'description' => "Menghapus kategori '{$name}' (ID: {$catId}) dari pusat dan cabang online.",
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return redirect()->route('admin.kategori.index')
                ->with('success', 'Kategori berhasil dihapus dari semua database.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }
}
