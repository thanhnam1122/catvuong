<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount(['partners', 'debts', 'invoices'])
            ->orderBy('name')
            ->get();

        return view('company.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Company::create($validated);

        return redirect()->back()->with('success', "Đã thêm doanh nghiệp [{$validated['name']}] thành công!");
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', "Cập nhật thông tin doanh nghiệp [{$company->name}] thành công!");
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $name = $company->name;
        $company->delete();

        return redirect()->back()->with('success', "Đã xóa doanh nghiệp [{$name}]!");
    }
}
