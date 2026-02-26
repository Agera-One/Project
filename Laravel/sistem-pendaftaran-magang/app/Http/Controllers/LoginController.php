<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }

    public function LoginValidate(Request $request)
    {
        // Validasi
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|min:6',
        ],
        [
            'login.required'    => 'Username atau email wajib diisi',
            'password.required' => 'Password wajib diisi',
            'password.min'      => 'Password minimal 8 karakter',
        ]);

        // Tentukan apakah input adalah email atau username
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return match (Auth::user()->role) {
                'siswa'         => redirect('/siswa/dashboard'),
                'perusahaan'    => redirect('/perusahaan/dashboard'),
                default => redirect('/login'),
            };
        }

        // Login gagal
        return back()
            ->withErrors(['login' => 'Username/email atau password salah.'])
            ->withInput($request->only('login'));
    }
}
