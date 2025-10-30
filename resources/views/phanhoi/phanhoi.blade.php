@foreach ($comments as $comment)
{{-- Tính toán margin inline cho Comment tĩnh (20px mỗi cấp) --}}
@php
    $marginPx = $level * 20;
@endphp

<div class="comment-item mb-2" id="comment-{{ $comment->id }}" style="margin-left: {{ $marginPx }}px;">
    <div class="d-flex align-items-start">
        <div>
            {{-- KHÔI PHỤC NỘI DUNG --}}
            <strong>{{ $comment->user->email ?? 'Người dùng ẩn danh' }}</strong>
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

            {{-- Container cho phản hồi con (Đệ quy) - SỬA LỖI: LUÔN RENDER CONTAINER NÀY --}}
            <div class="replies mt-2">
                @if ($comment->repliesRecursive && $comment->repliesRecursive->count())
                    @include('phanhoi.phanhoi', [
                        'comments' => $comment->repliesRecursive,
                        'level' => $level + 1
                    ])
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
