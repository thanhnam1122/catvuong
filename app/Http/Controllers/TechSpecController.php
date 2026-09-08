<?php

namespace App\Http\Controllers;

use App\Models\TechSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechSpecController extends Controller
{
    public function index(Request $request)
    {
        $query = TechSpec::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('specs', 'like', "%{$search}%");
            });
        }

        $specs = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('tech_specs.index', compact('specs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:150',
            'model' => 'required|string|max:150',
            'specs' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('tech_specs', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        TechSpec::create($validated);

        return redirect()->route('tech-specs.index')->with('success', 'Đã thêm mẫu Thông số kỹ thuật (Trang 2) thành công!');
    }

    public function update(Request $request, TechSpec $techSpec)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:150',
            'model' => 'required|string|max:150',
            'specs' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($techSpec->image_path && file_exists(public_path($techSpec->image_path))) {
                @unlink(public_path($techSpec->image_path));
            }

            $path = $request->file('image')->store('tech_specs', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $techSpec->update($validated);

        return redirect()->route('tech-specs.index')->with('success', 'Đã cập nhật thông số kỹ thuật!');
    }

    public function destroy(TechSpec $techSpec)
    {
        if ($techSpec->image_path && file_exists(public_path($techSpec->image_path))) {
            @unlink(public_path($techSpec->image_path));
        }

        $techSpec->delete();

        return redirect()->route('tech-specs.index')->with('success', 'Đã xóa mẫu thông số kỹ thuật thành công!');
    }

    public function apiList()
    {
        return response()->json(TechSpec::orderBy('brand')->orderBy('model')->get());
    }
}

