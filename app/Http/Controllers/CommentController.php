<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Review;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException; // ✅ Thêm để bắt lỗi
use Illuminate\Database\Eloquent\ModelNotFoundException; // ✅ Thêm để bắt lỗi

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    // 🟦 Lưu bình luận cho review
    public function store(Request $request, $reviewId)
    {
        // ======================================================
        // BƯỚC 1: VALIDATE NỘI DUNG (Controller)
        // ======================================================
        $validated = $request->validate([
            'content' => 'required|string|min:10|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        try {
            // ======================================================
            // BƯỚC 2: GỌI SERVICE ĐỂ TẠO (Service)
            // ======================================================
            $comment = $this->commentService->createComment(
                $reviewId,
                $validated['content'],
                $request->parent_id
            );

            // ======================================================
            // BƯỚC 3: TRẢ VỀ JSON (Controller)
            // ======================================================
            if ($request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'user'         => $comment->user->name ?? $comment->user->email,
                    'content'      => $comment->content,
                    'id'           => $comment->id,
                    'parent_id_db' => $comment->parent_id,
                    'created_at'   => $comment->created_at->diffForHumans(),
                ]);
            }

            return back();

        } catch (ModelNotFoundException $e) {
            // ======================================================
            // BƯỚC 4: BẮT LỖI NẾU REVIEW KHÔNG TỒN TẠI (Controller)
            // ======================================================
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() // Lấy message từ service
            ], 404);
        }
    }

    // 🟨 Lưu phản hồi cho comment (Reply)
    public function reply(Request $request, Comment $comment)
    {
        // 1. Validate
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // 2. Gọi Service
        $reply = $this->commentService->createReply(
            $comment, // Comment cha
            $validated['content']
        );

        // 3. Trả về JSON
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'id'         => $reply->id,
                'user'       => $reply->user->name ?? 'Người dùng',
                'content'    => $reply->content,
                'parent_id'  => $reply->parent_id,
                'created_at' => $reply->created_at->diffForHumans(),
            ]);
        }

        return back();
    }

    // 🟩 Hiển thị chi tiết review + toàn bộ comment (phân cấp)
    public function show($reviewId)
    {
        // 🚀 Lấy review cùng các bình luận (đã load đệ quy replies)
        // (Phần này là truy vấn, có thể giữ ở Controller hoặc đưa vào ReviewService)
        $review = Review::with([
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->with(['user', 'repliesRecursive.user']);
            },
            'user'
        ])->findOrFail($reviewId);

        return view('product.show', compact('review'));
    }

    // 🟧 Cập nhật comment
    public function update(Request $request, Comment $comment)
    {
        // 1. Validate
        $validated = $request->validate(['content' => 'required|string|min:1']);

        try {
            // 2. Gọi Service (Service sẽ tự kiểm tra quyền)
            $updatedComment = $this->commentService->updateComment(
                $comment,
                $validated['content'],
                auth()->user()
            );

            // 3. Trả về JSON
            return response()->json([
                'success' => true,
                'comment' => $updatedComment
            ]);

        } catch (AuthorizationException $e) {
            // 4. Bắt lỗi không có quyền
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    /**
     * [MỚI] Xóa một comment
     */
    public function destroy(Comment $comment)
    {
        try {
            // 1. Gọi Service (Service sẽ tự kiểm tra quyền)
            $this->commentService->deleteComment(
                $comment,
                auth()->user()
            );

            // 2. Trả về JSON
            return response()->json(['success' => true]);

        } catch (AuthorizationException $e) {
            // 3. Bắt lỗi không có quyền
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }
}