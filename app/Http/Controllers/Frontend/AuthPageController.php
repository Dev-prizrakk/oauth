<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthPageController extends Controller
{
    // Отображение страницы логина/регистрации
    public function index()
    {
        return view('auth'); // форма логина и регистрации
    }

    // Обработка логина
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // важно для безопасности
            return redirect()->intended('/profile');
        }

        return back()->withErrors([
            'email' => 'Неверные данные',
        ]);
    }

    // Обработка выхода
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/auth')->with('message', 'Вы вышли из системы');
        ;
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);

        return redirect('/profile')->with('message', 'Регистрация успешна');
    }

}
