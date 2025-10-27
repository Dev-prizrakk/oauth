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

<form method="POST" action="{{ route('auth.login') }}">
    @csrf
    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Пароль</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Войти</button>
</form>

<form method="POST" action="{{ route('auth.register') }}">
    @csrf
    <label for="reg_name">Имя</label>
    <input type="text" name="name" id="reg_name" required>

    <label for="reg_email">Email</label>
    <input type="email" name="email" id="reg_email" required>

    <label for="reg_password">Пароль</label>
    <input type="password" name="password" id="reg_password" required>

    <label for="password_confirmation">Подтвердите пароль</label>
    <input type="password" name="password_confirmation" id="password_confirmation" required>

    <button type="submit">Регистрация</button>
</form>
@endsection
