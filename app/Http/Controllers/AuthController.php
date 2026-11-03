<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Hiển thị giao diện đăng nhập
     */
    public function showLogin()
    {
        // Tự động khởi tạo tài khoản nếu DB chưa có
        if (User::count() === 0) {
            User::create([
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123'),
            ]);
        }

        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Vui lòng nhập tài khoản hoặc email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->boolean('remember');
        $input = trim($credentials['email']);
        $password = $credentials['password'];

        // Hỗ trợ đăng nhập linh hoạt bằng tài khoản "admin" hoặc email
        $attemptSuccess = false;
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $attemptSuccess = Auth::attempt(['email' => $input, 'password' => $password], $remember);
        } else {
            $attemptSuccess = Auth::attempt(['name' => $input, 'password' => $password], $remember);
            if (!$attemptSuccess) {
                $user = User::where('name', $input)->orWhere('email', 'like', $input . '@%')->first();
                if ($user && Hash::check($password, $user->password)) {
                    Auth::login($user, $remember);
                    $attemptSuccess = true;
                }
            }
        }

        if ($attemptSuccess) {
            $request->session()->regenerate();

            return redirect()->intended(route('invoice.in'))->with('success', 'Chào mừng bạn quay trở lại!');
        }

        return back()->withErrors([
            'email' => 'Tài khoản hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Bạn đã đăng xuất khỏi hệ thống thành công.');
    }
}

