@extends('layouts.app')

@section('content')
<h1>Новый пароль</h1>

<form id="reset-form">
    <label for="email">Email</label>
    <input type="email" id="email" required>

    <label for="token">Токен</label>
    <input type="text" id="token" required>

    <label for="password">Новый пароль</label>
    <input type="password" id="password" required>

    <label for="password_confirmation">Подтверждение пароля</label>
    <input type="password" id="password_confirmation" required>

    <button type="submit">Сбросить пароль</button>
</form>

<div id="response-message" class="alert" style="display:none;"></div>

<script>
document.getElementById('reset-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const token = document.getElementById('token').value;
    const password = document.getElementById('password').value;
    const password_confirmation = document.getElementById('password_confirmation').value;

    const res = await fetch('/api/v1/auth/password/reset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email, token, password, password_confirmation })
    });

    const data = await res.json();
    const msgDiv = document.getElementById('response-message');
    msgDiv.style.display = 'block';

    if (res.ok) {
        msgDiv.className = 'alert alert-success';
        msgDiv.textContent = '✅ Пароль успешно обновлён. Теперь можешь войти!';
    } else {
        msgDiv.className = 'alert alert-error';
        msgDiv.textContent = data.error || JSON.stringify(data.errors);
    }
});
</script>
@endsection
