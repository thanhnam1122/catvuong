<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'brand' => 'nullable|string|max:100',
            'model' => 'required|string|max:100',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'warranty_period' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Đã thêm sản phẩm / linh kiện mới!');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'brand' => 'nullable|string|max:100',
            'model' => 'required|string|max:100',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'warranty_period' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Đã cập nhật sản phẩm / linh kiện!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Đã xóa sản phẩm thành công!');
    }

    public function apiList()
    {
        return response()->json(Product::orderBy('brand')->orderBy('model')->get());
    }
}
