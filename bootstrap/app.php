<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý lỗi 404 (Không tìm thấy trang)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            
            // Nếu là request API (để tránh lỗi khi call API)
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.'
                ], 404);
            }

            // Nếu là request WEB (trình duyệt) -> Chuyển về trang chủ
            // Đảm bảo bạn đã đặt tên route trang chủ là 'home' hoặc thay bằng '/'
            return redirect()->route('home')
                ->with('error', 'Đường dẫn bạn truy cập không tồn tại!');
        });
    })->create();