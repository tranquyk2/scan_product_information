<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Hiển thị form login
    public function showLogin()
    {
        if (Auth::check() && Auth::user() && Auth::user()->is_admin) {
            return redirect()->route('admin.upload');
        }
        return view('auth.login');
    }

    // Xử lý login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Kiểm tra admin
            if (Auth::user() && Auth::user()->is_admin) {
                return redirect()->route('admin.upload');
            }
            
            // Nếu không phải admin, logout
            Auth::logout();
            return back()->withErrors(['email' => 'Tài khoản không có quyền admin']);
        }

        return back()->withErrors(['email' => 'Email hoặc password không đúng']);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
