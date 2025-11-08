
@foreach ($comments as $comment)
    {{-- Tính toán margin inline cho Comment tĩnh (20px mỗi cấp) --}}
    @php
        $marginPx = $level * 20;
    @endphp

    {{-- Thẻ div .comment-item cho MỖI comment --}}
    <div class="comment-item mb-2" id="comment-{{ $comment->id }}" style="margin-left: {{ $marginPx }}px;">
        <div class="d-flex align-items-start">
            <div>
                <strong>{{ $comment->user->name ?? 'Người dùng ẩn danh' }}</strong>

                {{-- [MỚI] Wrapper để chứa nội dung và form sửa --}}
                <div class="comment-content-wrapper" id="comment-content-wrapper-{{ $comment->id }}">
                    
                    {{-- 1. Nội dung gốc (hiện mặc định) --}}
                    <p class="mb-1" data-comment-content>{{ $comment->content }}</p>

                    {{-- 2. [MỚI] Form sửa (ẩn mặc định) --}}
                    @auth
                    <form id="edit-form-comment-{{ $comment->id }}" class="comment-edit-form d-none mt-2" 
                          onsubmit="event.preventDefault(); saveCommentEdit({{ $comment->id }});">
                        <textarea class="form-control form-control-sm" name="content" required>{{ $comment->content }}</textarea>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm">💾 Lưu</button>
                            <button type="button" class="btn btn-secondary btn-sm" 
                                    onclick="toggleCommentEditForm({{ $comment->id }})">❌ Hủy</button>
                        </div>
                    </form>
                    @endauth
                </div>
                {{-- Kết thúc wrapper --}}


                {{-- 3. Thanh công cụ (Phản hồi, Sửa, Xóa) --}}
                <small class="text-muted">
                    {{ $comment->created_at->diffForHumans() }}
                    
                    @auth
                    · <button class="btn btn-link btn-sm text-decoration-none p-0"
                            onclick="toggleReplyForm('comment-{{ $comment->id }}')">💬 Phản hồi</button>

                    {{-- [MỚI] Nút Sửa/Xóa, chỉ hiện khi đúng là chủ comment --}}
                    @if(auth()->id() === $comment->user_id)
                        · <button class="btn btn-link btn-sm text-decoration-none p-0 text-primary" 
                              onclick="toggleCommentEditForm({{ $comment->id }})">✏️ Sửa</button>
                        · <button class="btn btn-link btn-sm text-decoration-none p-0 text-danger" 
                              onclick="deleteComment({{ $comment->id }})">🗑️ Xóa</button>
                    @endif

                    @endauth
                </small>

                {{-- 4. Form phản hồi (cho comment này) --}}
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

                {{-- 5. Container cho phản hồi con (Đệ quy) --}}
                <div class="replies mt-2">
                    @if ($comment->replies && $comment->replies->count())
                        @include('phanhoi.phanhoi', [
                            'comments' => $comment->replies,
                            'level' => $level + 1,
                            'review_id' => $review_id // Đảm bảo truyền review_id xuống cấp con
                        ])
                    @endif
                </div>

            </div>
        </div>
    </div>
@endforeach
<script>
    function toggleCommentEditForm(commentId) {
    const wrapper = document.getElementById(`comment-content-wrapper-${commentId}`);
    if (!wrapper) return;

    const contentP = wrapper.querySelector('[data-comment-content]');
    const editForm = wrapper.querySelector('.comment-edit-form');

    contentP.classList.toggle('d-none');
    editForm.classList.toggle('d-none');
    
    // Nếu hiện form thì focus vào textarea
    if (!editForm.classList.contains('d-none')) {
        editForm.querySelector('textarea').focus();
    }
}

/**
 * [MỚI] Gửi yêu cầu LƯU (Update/PUT) comment
 */
async function saveCommentEdit(commentId) {
    const form = document.getElementById(`edit-form-comment-${commentId}`);
    const textarea = form.querySelector('textarea[name="content"]');
    const newContent = textarea.value;

    if (!newContent) {
        alert('Nội dung không được để trống.');
        return;
    }

    const saveButton = form.querySelector('[type="submit"]');
    saveButton.disabled = true;
    saveButton.textContent = 'Đang lưu...';

    try {
        const res = await fetch(`/comments/${commentId}`, { // Yêu cầu route PUT /comments/{id}
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content: newContent
            })
        });

        const data = await res.json();

        if (data.success) {
            // 1. Cập nhật nội dung mới vào thẻ <p>
            const wrapper = document.getElementById(`comment-content-wrapper-${commentId}`);
            wrapper.querySelector('[data-comment-content]').textContent = data.comment.content;
            
            // 2. Ẩn form sửa, hiện lại nội dung
            toggleCommentEditForm(commentId);
        } else {
            alert('Lỗi khi lưu: ' + (data.message || 'Bạn không có quyền sửa.'));
        }
    } catch (err) {
        console.error('Lỗi saveCommentEdit:', err);
        alert('Lỗi kết nối máy chủ.');
    } finally {
        saveButton.disabled = false;
        saveButton.textContent = '💾 Lưu';
    }
}

/**
 * [MỚI] Gửi yêu cầu XÓA (Delete/DELETE) comment
 */
async function deleteComment(commentId) {
    if (!confirm('Bạn có chắc muốn xóa bình luận này không? Mọi phản hồi con (nếu có) cũng sẽ bị xóa.')) return;

    try {
        const res = await fetch(`/comments/${commentId}`, { // Yêu cầu route DELETE /comments/{id}
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await res.json();

        if (data.success) {
            // Xóa toàn bộ div.comment-item khỏi DOM
            const commentDiv = document.getElementById(`comment-${commentId}`);
            if (commentDiv) {
                commentDiv.remove();
            }
            // (Bạn có thể thêm thông báo alert nếu muốn)
        } else {
            alert('Lỗi khi xóa: ' + (data.message || 'Bạn không có quyền xóa.'));
        }
    } catch (err) {
        console.error('Lỗi deleteComment:', err);
        alert('Lỗi kết nối máy chủ.');
    }
}
</script>