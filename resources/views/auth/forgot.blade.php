@extends('layouts.app')

@section('content')
<h1>Сброс пароля</h1>

<form id="forgot-form">
    <label for="email">Email</label>
    <input type="email" id="email" required>

    <button type="submit">Отправить ссылку для сброса</button>
</form>

<div id="response-message" class="alert" style="display:none;"></div>

<script>
document.getElementById('forgot-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;

    const res = await fetch('/api/v1/auth/password/forgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email })
    });

    const data = await res.json();
    const msgDiv = document.getElementById('response-message');
    msgDiv.style.display = 'block';

    if (res.ok) {
        msgDiv.className = 'alert alert-success';
        msgDiv.innerHTML = `✅ Токен: <code>${data.token}</code> (для теста).<br>Теперь перейди на <a href="/auth/reset">страницу сброса</a>`;
    } else {
        msgDiv.className = 'alert alert-error';
        msgDiv.textContent = data.error || JSON.stringify(data.errors);
    }
});
</script>
@endsection
