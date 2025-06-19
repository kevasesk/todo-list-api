<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Todo List App</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/auth.js'])
        @endif
    </head>
    <body>
        <h1>Welcome!</h1>

        @if (session('success'))
            <p style="color: green;">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p style="color: red;">{{ session('error') }}</p>
        @endif

        @if ($errors->any())
            <div style="color: red;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" data-user-login-form>
            @csrf
            <label for="name_login">Name:</label><br>
            <input type="text" id="name_login" name="name" value="{{ old('name') }}" required><br><br>

            <label for="password_login">Password:</label><br>
            <input type="password" id="password_login" name="password" required><br><br>

            <button type="button" data-user-login-button>Login</button>
            <p><button type="button" data-switch-to-register>Don't have an account? Register</button></p>
        </form>

        <form method="POST" data-user-register-form class="hidden">
            @csrf
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required><br><br>

            <label for="password_register">Password:</label><br>
            <input type="password" id="password_register" name="password" required><br><br>

            <label for="password_confirmation">Confirm Password:</label><br>
            <input type="password" id="password_confirmation" name="password_confirmation" required><br><br>

            <button type="submit" data-user-register-button>Register</button>
            <p><button type="button" data-switch-to-login>Already have an account? Login</button></p>
        </form>

    </body>
</html>
