<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Review; // ✅ Thêm dòng này để tránh lỗi Review not found
use App\Services\CommentService;
use Illuminate\Http\Request;

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
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $this->commentService->createComment(
            $reviewId,
            $request->content,
            $request->parent_id
        );

        if ($request->ajax()) {
            return response()->json([
                'success'   => true,
                'user'      => auth()->user()->email,
                'content'   => $comment->content,
                'id'        => $comment->id,
                'parent_id' => $comment->parent_id,
                'created_at'=> $comment->created_at->diffForHumans(),
            ]);
        }

        return back();
    }

    // 🟨 Lưu phản hồi cho comment
    public function reply(Request $request, Comment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $reply = Comment::create([
            'review_id' => $comment->review_id,
            'user_id'   => auth()->id(),
            'content'   => $request->content,
            'parent_id' => $comment->id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success'   => true,
                'id'        => $reply->id,
                'user'      => auth()->user()->name ?? 'Người dùng',
                'content'   => $reply->content,
                'parent_id' => $reply->parent_id,
                'created_at'=> $reply->created_at->diffForHumans(),
            ]);
        }

        return back();
    }

    // 🟩 Hiển thị chi tiết review + toàn bộ comment (phân cấp)
    public function show($reviewId)
    {
        // 🚀 Lấy review cùng các bình luận (đã load đệ quy replies)
        $review = Review::with([
            // ✅ Lấy comment cấp 1
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                      ->with(['user', 'repliesRecursive.user']);
            },
            'user'
        ])->findOrFail($reviewId);
        return view('product.show', compact('review'));
    }
    public function update(Request $request, Comment $comment)
    {
        // 1. Kiểm tra quyền: Chỉ chủ comment mới được sửa
        if (auth()->id() !== $comment->user_id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }

        // 2. Validate
        $request->validate(['content' => 'required|string|min:1']);

        // 3. Cập nhật
        $comment->update([
            'content' => $request->content
        ]);

        // 4. Trả về JSON cho JavaScript
        return response()->json([
            'success' => true,
            'comment' => $comment // Gửi lại comment đã cập nhật
        ]);
    }

    /**
     * [MỚI] Xóa một comment
     */
    public function destroy(Comment $comment)
    {
        // 1. Kiểm tra quyền: Chỉ chủ comment mới được xóa
        // (Hoặc bạn có thể cho phép chủ review cũng được xóa)
        if (auth()->id() !== $comment->user_id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }
        
        // 2. Xóa
        // Model Comment sẽ tự động xóa các 'replies' con nếu bạn đã 
        // thiết lập 'onDelete('cascade')' trong migration.
        // Nếu không, bạn cần xóa đệ quy.
        $comment->delete();

        // 3. Trả về JSON
        return response()->json(['success' => true]);
    }
}
