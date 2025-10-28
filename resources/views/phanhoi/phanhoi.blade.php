@foreach ($comments as $comment)
<div class="comment-item mb-2 ms-{{ $level * 4 }}">
    <div class="d-flex align-items-start">
        <div>
            <strong>{{ $comment->user->email ?? 'Người dùng' }}</strong>
            <p class="mb-1">{{ $comment->content }}</p>
            <small class="text-muted">
                {{ $comment->created_at->diffForHumans() }}
                · <button class="btn btn-link btn-sm text-decoration-none p-0"
                    onclick="toggleReplyForm('comment-{{ $comment->id }}')">Phản hồi</button>
            </small>

            {{-- Form phản hồi cho comment này --}}
            @auth
            <form id="reply-form-comment-{{ $comment->id }}" class="reply-form d-none mt-1"
                action="{{ route('comments.store', $comment->review_id) }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="input-group input-group-sm">
                    <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                    <button class="btn btn-outline-primary" type="submit">Gửi</button>
                </div>
            </form>
            @endauth

            {{-- ✅ Đây là nơi gọi đệ quy --}}
            @if ($comment->repliesRecursive && $comment->repliesRecursive->count())
                <div class="replies ms-4 mt-2">
                    @include('phanhoi.phanhoi', [
                        'comments' => $comment->repliesRecursive,
                        'level' => $level + 1
                    ])
                </div>
            @endif
        </div>
    </div>
</div>
@endforeach
