<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use App\Helpers\DatabaseHelper;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Product::with('category');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(10);
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $branches = Branch::all();
        return view('admin.products.create', compact('categories', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|unique:products,barcode',
            'sku' => 'required|unique:products,sku',
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'branch_id' => 'nullable|integer',
            'image_url' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Create product on Central database
                $product = Product::create($request->only(['barcode', 'sku', 'name', 'category_id', 'buy_price', 'sell_price', 'image_url']));

                $stock = $request->stock;
                $targetBranchId = $request->branch_id;

                // 2. Replicate this product to selected or all branch nodes
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    $branchId = $branch->id;

                    // If a specific branch is targeted, skip other branches
                    if ($targetBranchId && $branchId != $targetBranchId) {
                        continue;
                    }
                    
                    if (DatabaseHelper::isNodeOnline($branchId)) {
                        $conn = DatabaseHelper::getConnectionName($branchId);
                        
                        DB::connection($conn)->table('products')->updateOrInsert(
                            ['id' => $product->id],
                            [
                                'barcode' => $product->barcode,
                                'sku' => $product->sku,
                                'name' => $product->name,
                                'category_id' => $product->category_id,
                                'buy_price' => $product->buy_price,
                                'sell_price' => $product->sell_price,
                                'image_url' => $product->image_url,
                                'created_at' => $product->created_at,
                                'updated_at' => $product->updated_at,
                            ]
                        );

                        // Seed initial inventory stock for this product on the node
                        DB::connection($conn)->table('inventory')->updateOrInsert(
                            ['product_id' => $product->id, 'branch_id' => $branchId],
                            ['stock' => $stock, 'minimum_stock' => 10, 'created_at' => now(), 'updated_at' => now()]
                        );

                        // Also update inventory in Central
                        DB::connection('mysql')->table('inventory')->updateOrInsert(
                            ['product_id' => $product->id, 'branch_id' => $branchId],
                            ['stock' => $stock, 'minimum_stock' => 10, 'created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }

                // Log activity
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity' => 'Tambah Produk',
                    'description' => "Menambahkan produk baru '{$product->name}' (SKU: {$product->sku}) dengan stok {$stock} di " . ($targetBranchId ? "Cabang ID {$targetBranchId}" : "Semua Cabang") . ".",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan dan direplikasi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $branches = Branch::all();
        
        // Find current stock details in Central database (as a reference)
        $stocks = DB::connection('mysql')->table('inventory')
            ->where('product_id', $id)
            ->pluck('stock', 'branch_id')
            ->toArray();

        return view('admin.products.edit', compact('product', 'categories', 'branches', 'stocks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'barcode' => 'required|unique:products,barcode,' . $product->id,
            'sku' => 'required|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $product) {
                // 1. Update product on Central
                $product->update($request->only(['barcode', 'sku', 'name', 'category_id', 'buy_price', 'sell_price', 'image_url']));

                // 2. Replicate changes to all ONLINE branch nodes
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    $branchId = $branch->id;
                    
                    if (DatabaseHelper::isNodeOnline($branchId)) {
                        $conn = DatabaseHelper::getConnectionName($branchId);
                        
                        DB::connection($conn)->table('products')->where('id', $product->id)->update([
                            'barcode' => $product->barcode,
                            'sku' => $product->sku,
                            'name' => $product->name,
                            'category_id' => $product->category_id,
                            'buy_price' => $product->buy_price,
                            'sell_price' => $product->sell_price,
                            'image_url' => $product->image_url,
                            'updated_at' => $product->updated_at,
                        ]);
                    }
                }

                // If stock is passed per branch, update it in Central and Online Branches
                if ($request->has('stocks')) {
                    foreach ($request->stocks as $bId => $stockValue) {
                        if ($stockValue !== null && $stockValue !== '') {
                            // Update Central
                            DB::connection('mysql')->table('inventory')->updateOrInsert(
                                ['product_id' => $product->id, 'branch_id' => $bId],
                                ['stock' => $stockValue, 'updated_at' => now()]
                            );

                            // Update Branch node if online
                            if (DatabaseHelper::isNodeOnline($bId)) {
                                $conn = DatabaseHelper::getConnectionName($bId);
                                DB::connection($conn)->table('inventory')->updateOrInsert(
                                    ['product_id' => $product->id, 'branch_id' => $bId],
                                    ['stock' => $stockValue, 'updated_at' => now()]
                                );
                            }
                        }
                    }
                }

                // Log activity
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity' => 'Edit Produk',
                    'description' => "Mengubah produk '{$product->name}' (SKU: {$product->sku}) dan memperbarui replikanya di cabang online.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diubah.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah produk: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        try {
            DB::transaction(function () use ($product) {
                // 1. Delete on Central
                $product->delete();

                // 2. Replicate delete (or soft delete) on all ONLINE branch nodes
                $branches = Branch::all();
                foreach ($branches as $branch) {
                    $branchId = $branch->id;
                    
                    if (DatabaseHelper::isNodeOnline($branchId)) {
                        $conn = DatabaseHelper::getConnectionName($branchId);
                        
                        // We do soft delete by setting deleted_at
                        DB::connection($conn)->table('products')->where('id', $product->id)->update([
                            'deleted_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Log activity
                DB::connection('mysql')->table('activity_logs')->insert([
                    'activity' => 'Hapus Produk',
                    'description' => "Menghapus produk '{$product->name}' (SKU: {$product->sku}) secara logis dari seluruh sistem.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            return back()->with('success', 'Produk berhasil dihapus dari pusat dan cabang online.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }
}
