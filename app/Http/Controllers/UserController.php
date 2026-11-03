<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Tạo tài khoản mới cho CTY
     */
    public function store(Request $request)
    {
        // Chỉ admin mới có quyền tạo tài khoản
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:users,name',
            'company_id' => 'required|exists:companies,id',
            'password' => 'required|string|min:3|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập tên tài khoản.',
            'name.unique' => 'Tên tài khoản này đã tồn tại, vui lòng chọn tên khác.',
            'company_id.required' => 'Vui lòng chọn công ty (CTY) cho tài khoản này.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 3 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $company = Company::findOrFail($validated['company_id']);

        // Tạo email nội bộ tương ứng với tài khoản
        $safeUsername = preg_replace('/[^a-zA-Z0-9_\.]/', '', strtolower($validated['name']));
        $companySuffix = $company->code ? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $company->code)) : 'cty' . $company->id;
        $generatedEmail = $safeUsername . '@' . $companySuffix . '.local';

        // Đảm bảo email duy nhất
        if (User::where('email', $generatedEmail)->exists()) {
            $generatedEmail = $safeUsername . '_' . rand(100, 999) . '@' . $companySuffix . '.local';
        }

        User::create([
            'name' => trim($validated['name']),
            'email' => $generatedEmail,
            'password' => Hash::make($validated['password']),
            'role' => 'company',
            'company_id' => $company->id,
        ]);

        return redirect()->back()->with('success', "Đã tạo tài khoản [{$validated['name']}] cho công ty [{$company->name}] thành công!");
    }

    /**
     * Xóa tài khoản
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $user = User::findOrFail($id);

        // Không cho phép tự xóa chính mình hoặc tài khoản admin
        if ($user->id === Auth::id() || $user->role === 'admin') {
            return redirect()->back()->with('error', 'Không thể xóa tài khoản Quản trị viên chính.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Đã xóa tài khoản [{$name}] thành công!");
    }
}

