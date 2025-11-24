<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class HandlePostTooLarge
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('HandlePostTooLarge middleware executing');
        
        try {
            return $next($request);
        } catch (PostTooLargeException $e) {
            Log::info('PostTooLargeException caught in middleware');
            
            $message = 'Tổng dung lượng file tải lên vượt quá giới hạn cho phép. Vui lòng chọn tối đa 5 ảnh, mỗi ảnh không quá 8MB.';
            
            Log::info('Attempting to redirect with flash message', ['message' => $message]);
            
            return Redirect::back()
                ->withInput($request->except('images'))
                ->with('post_too_large_error', $message);
        }
    }
}
