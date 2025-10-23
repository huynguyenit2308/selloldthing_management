<?php

namespace App\Http\Controllers;

use App\Models\Comment;
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
                'success' => true,
                'user' => auth()->user()->email,
                'content' => $comment->content,
                'id' => $comment->id,
                'created_at' => $comment->created_at->diffForHumans(),
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

        $reply = $this->commentService->createReply($comment, $request->content);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => auth()->user()->name ?? auth()->user()->email,
                'content' => $reply->content,
                'created_at' => $reply->created_at->diffForHumans(),
            ]);
        }

        return back();
    }
}
