@extends('layouts.app')

@section('content')
<h1>Добро пожаловать!</h1>
<nav>
    <a href="{{ route('auth.page') }}">Вход / Регистрация</a>
    <a href="{{ route('profile') }}">Профиль</a>
</nav>
@endsection
