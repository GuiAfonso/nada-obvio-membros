<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f7f7f5;
            color: #1a1a1a;
            min-height: 100vh;
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid #ebebeb;
            padding: 0 2rem;
            height: 56px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
            letter-spacing: -.01em;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-user {
            font-size: 0.85rem;
            color: #888;
        }

        .btn-logout {
            background: none;
            border: 1px solid #e0e0e0;
            padding: 0.35rem 0.85rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.82rem;
            color: #666;
            transition: background 0.15s;
        }

        .btn-logout:hover { background: #f5f5f5; color: #1a1a1a; }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        @yield('styles')
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">Club Nada Óbvio</a>
        <div class="navbar-right">
            <span class="navbar-user">{{ session('user.nome') }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sair</button>
            </form>
        </div>
    </nav>

    <div class="page">
        @yield('content')
    </div>
</body>
</html>
