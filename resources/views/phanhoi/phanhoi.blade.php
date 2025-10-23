@push('styles')
<link rel="stylesheet" href="{{ asset('styles/product-detail.css') }}">
<link rel="stylesheet" href="{{ asset('styles/review.css') }}">
@endpush
@foreach ($comments as $comment)
    <div class="comment-item mb-2 ms-{{ $level ?? 0 }}">
        <div class="d-flex align-items-start">
            <div>
                <strong>{{ $comment->user->name ?? 'Người dùng ẩn danh' }}</strong>
                <p class="mb-1">{{ $comment->content }}</p>
                <small class="text-muted">
                    {{ $comment->created_at->diffForHumans() }}
                    · <button class="btn btn-link btn-sm text-decoration-none p-0" onclick="toggleReplyForm('comment-{{ $comment->id }}')">Phản hồi</button>
                </small>

                <!-- Form phản hồi -->
                <form id="reply-form-comment-{{ $comment->id }}" class="reply-form d-none mt-1" action="{{ route('comments.reply', $comment->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                        <button class="btn btn-outline-primary" type="submit">Gửi</button>
                    </div>
                </form>

                {{-- 🟦 Hiển thị phản hồi con --}}
                @if($comment->replies && $comment->replies->count())
                    <div class="mt-2 ms-4">
                        @include('phanhoi.phanhoi', ['comments' => $comment->replies, 'level' => ($level ?? 0) + 1])
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach
