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
