@extends('layouts.app')

@section('content')
<h1>Профиль пользователя</h1>

@if(!$user->email_verified_at)
<div class="alert alert-warning" style="display: flex; align-items: center; justify-content: space-between; max-width: 500px; margin: 20px auto; padding: 15px; background: #f9e5e5; border-radius: 8px;">
    <span>Email не подтверждён. Подтвердите, чтобы использовать все возможности.</span>
    <form method="POST" action="{{ route('email.verify.frontend') }}" style="margin: 0;">
        @csrf
        <button type="submit" style="padding: 8px 15px; background: #27ae60; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            Подтвердить Email
        </button>
    </form>
</div>
@endif

<p><strong>Имя:</strong> {{ $user->name }}</p>
<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>Подтвержден:</strong> {{ $user->email_verified_at ? 'Да' : 'Нет' }}</p>

{{-- 🔹 Если пользователь вошёл, но забыл пароль --}}
<div style="margin-top: 20px; text-align: center;">
    <p style="color: #555;">Забыли пароль? Вы можете сбросить его:</p>
    <a href="{{ route('auth.forgot') }}" style="display: inline-block; padding: 10px 15px; background: #e67e22; color: #fff; border-radius: 5px; text-decoration: none;">
        Сбросить пароль
    </a>
</div>

<form method="POST" action="{{ route('auth.logout') }}" style="margin-top: 30px; text-align: center;">
    @csrf
    <button type="submit" style="padding: 10px 20px; background: #3498db; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
        Выйти
    </button>
</form>
@endsection
