<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Регистрация
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->generateToken();

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // Логин
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Неверные учетные данные'], 401);
        }

        $token = $user->generateToken();

        return response()->json([
            'message' => 'Успешный вход',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // Выход
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->revokeToken();
        }

        return response()->json(['message' => 'Выход выполнен успешно']);
    }

    // Текущий пользователь
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
