<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .field { margin-bottom: 1.25rem; }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        input {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.15s;
        }

        input:focus { border-color: #1a1a1a; }

        input.error { border-color: #e53e3e; }

        .error-msg {
            font-size: 0.8rem;
            color: #e53e3e;
            margin-top: 0.375rem;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .btn:hover { background: #333; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ config('app.name') }}</h1>
        <p class="subtitle">Acesse sua área de membros</p>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="field">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    class="{{ $errors->has('email') ? 'error' : '' }}"
                    autofocus
                >
                @error('email')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    autocomplete="current-password"
                    class="{{ $errors->has('senha') ? 'error' : '' }}"
                >
                @error('senha')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</body>
</html>
