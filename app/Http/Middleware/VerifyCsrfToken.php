<?php

namespace App\Http\Middleware;

class VerifyCsrfToken
{
    protected array $except = [
        'telegram/webhook',
        '/telegraph/webhook',
        // или, если у вас в конфиге путь с токеном, например:
         '/telegraph/{token}/webhook',
        'telegraph-webhook/*',
    ];

}
