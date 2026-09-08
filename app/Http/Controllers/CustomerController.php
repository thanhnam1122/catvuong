<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('tel', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'social_contact' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm khách hàng mới thành công!',
                'customer' => $customer
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Đã thêm khách hàng mới thành công!');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'social_contact' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Đã cập nhật thông tin khách hàng!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Đã xóa khách hàng thành công!');
    }

    public function apiList()
    {
        return response()->json(Customer::orderBy('name')->get());
    }
}
