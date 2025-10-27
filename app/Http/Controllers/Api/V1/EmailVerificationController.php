<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    // 📤 POST /api/v1/auth/email-verify/{user}
    // Отправка ссылки подтверждения email
    /**
     * @OA\Post(
     *     path="/api/v1/auth/email-verify/{user}",
     *     tags={"Authentication"},
     *     summary="Отправить ссылку для подтверждения email",
     *     description="Создает токен и возвращает ссылку для подтверждения email указанного пользователя.",
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID пользователя",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ссылка создана успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ссылка для подтверждения email создана."),
     *             @OA\Property(property="verify_url", type="string", example="http://localhost/api/v1/auth/email-verify?user=1&token=abc123")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Email уже подтверждён"),
     *     @OA\Response(response=404, description="Пользователь не найден")
     * )
     */

    public function sendLink(Request $request, User $user)
    {
        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email уже подтверждён'], 400);
        }

        // Генерация токена
        $token = Str::random(64);

        DB::table('email_verifications')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // В реальном проекте — отправка письма со ссылкой.
        // Для демонстрации просто возвращаем ссылку в ответе.
        $verifyUrl = url("/api/v1/auth/email-verify?user={$user->id}&token={$token}");

        return response()->json([
            'message' => 'Ссылка для подтверждения email создана.',
            'verify_url' => $verifyUrl,
        ]);
    }

    // ✅ POST /api/v1/auth/email-verify
    // Подтверждение email по токену
    /**
     * @OA\Post(
     *     path="/api/v1/auth/email-verify",
     *     tags={"Authentication"},
     *     summary="Подтвердить email по токену",
     *     description="Проверяет токен подтверждения email и устанавливает дату верификации.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user","token"},
     *             @OA\Property(property="user", type="integer", example=1, description="ID пользователя"),
     *             @OA\Property(property="token", type="string", example="abc123", description="Токен подтверждения email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email успешно подтверждён"),
     *     @OA\Response(response=400, description="Неверный или истёкший токен"),
     *     @OA\Response(response=404, description="Запрос подтверждения не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|integer|exists:users,id',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = DB::table('email_verifications')->where('user_id', $request->user)->first();

        if (!$record) {
            return response()->json(['error' => 'Запрос подтверждения не найден'], 404);
        }

        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['error' => 'Неверный токен'], 400);
        }

        if (Carbon::parse($record->created_at)->addHours(2)->isPast()) {
            return response()->json(['error' => 'Срок действия токена истек'], 400);
        }

        // Обновляем пользователя
        $user = User::find($request->user);
        $user->email_verified_at = now();
        $user->save();

        // Удаляем токен
        DB::table('email_verifications')->where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Email успешно подтверждён']);
    }
}
