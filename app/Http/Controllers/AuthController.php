<?php

namespace App\Http\Controllers;

use App\Services\NotionService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private NotionService $notion) {}

    public function showLogin()
    {
        if (session()->has('user')) {
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
            'email.email'    => 'E-mail inválido.',
            'senha.required' => 'Informe sua senha.',
        ]);

        $user = $this->notion->findUserByEmail($request->email);

        if (! $user) {
            return back()->withErrors(['email' => 'Usuário não encontrado.'])->withInput();
        }

        $properties = $user['properties'] ?? [];

        $ativo = $properties['Ativo']['checkbox'] ?? false;
        if (! $ativo) {
            return back()->withErrors(['email' => 'Sua conta está inativa. Entre em contato com o suporte.'])->withInput();
        }

        $senhaNotion = $properties['Senha']['rich_text'][0]['plain_text'] ?? '';
        if ($request->senha !== $senhaNotion) {
            return back()->withErrors(['senha' => 'Senha incorreta.'])->withInput();
        }

        $nome = $properties['Nome']['title'][0]['plain_text'] ?? 'Membro';
        $email = $properties['email']['email'] ?? $request->email;

        $request->session()->regenerate();
        $request->session()->put('user', [
            'id'    => $user['id'],
            'nome'  => $nome,
            'email' => $request->email,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
