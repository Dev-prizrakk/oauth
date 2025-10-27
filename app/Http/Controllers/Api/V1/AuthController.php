<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/registration",
     *     tags={"Authentication"},
     *     summary="Регистрация пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="user@example.com"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Пользователь успешно зарегестрирован"),
     *     @OA\Response(response=422, description="Неверные данные")
     * )
     */
    // Регистрация
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->generateToken();

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Authentication"},
     *     summary="Авторизация пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Успешный вход"),
     *     @OA\Response(response=401, description="Неверные учетные данные"),
     *     @OA\Response(response=422, description="Неверные данные")
     * )
     */
    // Логин
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
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
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/auth/logout",
     *     tags={"Authentication"},
     *     summary="Выход пользователя",
     *     description="Отзывает токен текущего пользователя и завершает сессию авторизации.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Выход выполнен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Выход выполнен успешно")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Пользователь не авторизован")
     * )
     */
    // Выход
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->revokeToken();
        }

        return response()->json(['message' => 'Выход выполнен успешно']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     tags={"Authentication"},
     *     summary="Получить информацию о текущем пользователе",
     *     description="Возвращает данные авторизованного пользователя по токену.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Информация о пользователе",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T12:34:56Z")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Пользователь не авторизован")
     * )
     */
    // Текущий пользователь
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
