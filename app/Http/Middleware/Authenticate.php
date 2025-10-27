<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Определяем, куда редиректить неавторизованного пользователя.
     * В API это не нужно, просто возвращаем 401.
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // abort сразу возвращает JSON для API
            abort(401, 'Неавторизован');
        }
    }
}
