<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ], [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'E-mail inválido.',
            'senha.required' => 'Informe sua senha.',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->senha,
        ];

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'E-mail ou senha inválidos.'])->withInput();
        }

        if (! Auth::user()->ativo) {
            Auth::logout();

            return back()->withErrors(['email' => 'Sua conta está inativa. Entre em contato com o suporte.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
