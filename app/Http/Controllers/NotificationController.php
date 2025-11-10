<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Lấy danh sách thông báo của user (API)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);
            
            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'unread_count' => Notification::getUnreadCount($user->id),
                'total' => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách thông báo', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách thông báo'
            ], 500);
        }
    }

    /**
     * Lấy số lượng thông báo chưa đọc
     */
    public function getUnreadCount()
    {
        try {
            $count = Notification::getUnreadCount(Auth::id());
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy số thông báo chưa đọc', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * Đánh dấu thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu là đã đọc'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi đánh dấu thông báo đã đọc', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể đánh dấu thông báo'
            ], 500);
        }
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead()
    {
        try {
            $this->notificationService->markAllAsRead(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả là đã đọc'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi đánh dấu tất cả thông báo đã đọc', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể đánh dấu thông báo'
            ], 500);
        }
    }

    /**
     * Xóa thông báo
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thông báo'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa thông báo', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa thông báo'
            ], 500);
        }
    }

    /**
     * Xóa tất cả thông báo
     */
    public function destroyAll()
    {
        try {
            Notification::where('user_id', Auth::id())->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tất cả thông báo'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa tất cả thông báo', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa thông báo'
            ], 500);
        }
    }
}
