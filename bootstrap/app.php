<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Session;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandlePostTooLarge::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $exception, $request) {
            \Log::info('PostTooLargeException handler triggered', ['url' => $request->fullUrl()]);
            
            $message = 'Tổng dung lượng file tải lên vượt quá giới hạn cho phép. Vui lòng chọn tối đa 5 ảnh, mỗi ảnh không quá 8MB.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => ['images' => [$message]],
                ], 422);
            }

            // Redirect to create page with error in query string
            return redirect()->route('products.create', ['error' => 'post_too_large']);
        });
        
        // Log all exceptions to see what's actually happening
        $exceptions->render(function (\Throwable $e, $request) {
            if ($e instanceof PostTooLargeException) {
                \Log::info('PostTooLargeException caught in global handler');
            }
            throw $e;
        });
    })->create();
