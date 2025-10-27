<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmailVerificationFrontendController extends Controller
{
    public function verify()
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            return redirect()->back()->with('message', 'Email уже подтверждён');
        }

        // Генерация токена
        $token = Str::random(64);

        // Сохраняем хэш токена
        DB::table('email_verifications')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Сразу проверяем токен и подтверждаем email
        $record = DB::table('email_verifications')->where('user_id', $user->id)->first();

        if ($record && Hash::check($token, $record->token) && Carbon::parse($record->created_at)->addHours(2)->isFuture()) {
            $user->email_verified_at = now();
            $user->save();
            DB::table('email_verifications')->where('user_id', $user->id)->delete();

            return redirect()->back()->with('message', 'Email успешно подтверждён!');
        }

        return redirect()->back()->with('message', 'Не удалось подтвердить email, попробуйте снова.');
    }
}
