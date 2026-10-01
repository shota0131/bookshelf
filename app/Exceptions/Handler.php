<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // 認可エラー時のJSONレスポンス
        $this->renderable(function (
            AccessDeniedHttpException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'この操作を実行する権限がありません。',
                ], 403);
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
