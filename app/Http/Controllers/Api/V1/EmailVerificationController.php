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
