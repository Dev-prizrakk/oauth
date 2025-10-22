<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs, которые должны быть исключены из проверки CSRF
     *
     * @var array
     */
    protected $except = [
        'api/v1/auth/*', // все маршруты API без CSRF
    ];
}
