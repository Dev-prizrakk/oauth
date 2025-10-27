<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordController extends Controller
{
    // Шаг 1: Запрос сброса пароля (отправка токена)
    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/forgot",
     *     tags={"Authentication"},
     *     summary="Создать токен для сброса пароля",
     *     description="Генерирует токен для сброса пароля и возвращает его (в тестовых целях).",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка для сброса пароля создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ссылка для сброса пароля успешно создана."),
     *             @OA\Property(property="token", type="string", example="abc123")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Неверные данные"),
     *     @OA\Response(response=404, description="Пользователь не найден")
     * )
     */

    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // 🔹 В реальном проекте — тут отправка письма, а пока просто вернем токен:
        return response()->json([
            'message' => 'Ссылка для сброса пароля успешно создана.',
            'token' => $token, // Только для теста — в продакшене не возвращаем токен!
        ]);
    }

    // Шаг 2: Проверка токена
    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/token-check",
     *     tags={"Authentication"},
     *     summary="Проверить токен сброса пароля",
     *     description="Проверяет, действителен ли токен, созданный для сброса пароля.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="abc123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Токен действителен"),
     *     @OA\Response(response=400, description="Неверный или истёкший токен"),
     *     @OA\Response(response=404, description="Запрос не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function tokenCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json(['error' => 'Запрос на сброс не найден'], 404);
        }

        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['error' => 'Неверный токен'], 400);
        }

        // Проверка срока действия (1 час)
        if (Carbon::parse($record->created_at)->addHour()->isPast()) {
            return response()->json(['error' => 'Срок действия токена истек'], 400);
        }

        return response()->json(['message' => 'Токен действителен']);
    }

    // Шаг 3: Сброс пароля
    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/reset",
     *     tags={"Authentication"},
     *     summary="Сбросить пароль пользователя",
     *     description="Позволяет задать новый пароль при наличии действительного токена.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="abc123"),
     *             @OA\Property(property="password", type="string", example="newPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newPassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Пароль успешно обновлён"),
     *     @OA\Response(response=400, description="Неверный токен или email"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['error' => 'Неверный токен или email'], 400);
        }

        if (Carbon::parse($record->created_at)->addHour()->isPast()) {
            return response()->json(['error' => 'Срок действия токена истек'], 400);
        }

        // Обновляем пароль
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Удаляем токен
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Пароль успешно обновлён']);
    }
}
