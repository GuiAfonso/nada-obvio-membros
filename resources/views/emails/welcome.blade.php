<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo(a) — {{ config('app.name') }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f7f7f5; color: #1a1a1a; padding: 2rem;">
    <table role="presentation" width="100%" style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; border: 1px solid #ebebeb; padding: 2rem;">
        <tr>
            <td>
                <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Bem-vindo(a) ao {{ config('app.name') }}!</h1>
                <p style="font-size: 0.95rem; line-height: 1.5; color: #444;">
                    Olá, {{ $user->name }}. Sua compra foi confirmada e seu acesso à área de membros já está liberado. Use as credenciais abaixo para entrar:
                </p>
                <table role="presentation" style="width: 100%; margin: 1.25rem 0; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem 0; font-size: 0.85rem; color: #888;">E-mail</td>
                        <td style="padding: 0.5rem 0; font-size: 0.95rem; font-weight: 600;">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-size: 0.85rem; color: #888;">Senha</td>
                        <td style="padding: 0.5rem 0; font-size: 0.95rem; font-weight: 600;">{{ $password }}</td>
                    </tr>
                </table>
                <a href="{{ route('login') }}" style="display: inline-block; background: #1a1a1a; color: #fff; text-decoration: none; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600;">
                    Acessar área de membros
                </a>
            </td>
        </tr>
    </table>
</body>
</html>
