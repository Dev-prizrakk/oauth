@extends('layouts.app')

@section('content')
<h1>Авторизация / Регистрация</h1>

@if($errors->any())
<div class="errors">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="auth-container" style="display: flex; gap: 40px; justify-content: center; flex-wrap: wrap;">
    {{-- 🔹 Авторизация --}}
    <div class="login-box" style="min-width: 300px;">
        <h2>Войти</h2>
        <form method="POST" action="{{ route('auth.login') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Пароль</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Войти</button>
        </form>

        <p style="margin-top: 10px; text-align: center;">
            <a href="{{ route('auth.forgot') }}" style="color: #3498db;">Забыли пароль?</a>
        </p>
    </div>

    {{-- 🔹 Регистрация --}}
    <div class="register-box" style="min-width: 300px;">
        <h2>Регистрация</h2>
        <form method="POST" action="{{ route('auth.register') }}">
            @csrf
            <label for="reg_name">Имя</label>
            <input type="text" name="name" id="reg_name" required>

            <label for="reg_email">Email</label>
            <input type="email" name="email" id="reg_email" required>

            <label for="reg_password">Пароль</label>
            <input type="password" name="password" id="reg_password" required>

            <label for="password_confirmation">Подтверждение пароля</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>

            <button type="submit">Регистрация</button>
        </form>
    </div>
</div>
@endsection
