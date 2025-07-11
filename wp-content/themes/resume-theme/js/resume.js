jQuery(document).ready(function($) {
    // 자격증 미리보기 모달
    $('.certification-preview-btn').on('click', function(e) {
        e.preventDefault();
        const fileUrl = $(this).attr('href');
        const fileName = $(this).data('filename') || '자격증';
        
        // 모달 HTML 생성
        const modalHtml = `
            <div class="cert-preview-modal" id="certPreviewModal">
                <div class="cert-preview-overlay"></div>
                <div class="cert-preview-container">
                    <div class="cert-preview-header">
                        <h3>${fileName} 미리보기</h3>
                        <button class="cert-preview-close">&times;</button>
                    </div>
                    <div class="cert-preview-content">
                        <iframe src="${fileUrl}" width="100%" height="600px" frameborder="0"></iframe>
                    </div>
                    <div class="cert-preview-footer">
                        <a href="${fileUrl}" target="_blank" class="btn btn-primary">새 창에서 열기</a>
                    </div>
                </div>
            </div>
        `;
        
        // 모달을 body에 추가
        $('body').append(modalHtml);
        
        // 모달 표시
        $('#certPreviewModal').fadeIn(300);
        
        // 닫기 버튼 이벤트
        $('.cert-preview-close, .cert-preview-overlay').on('click', function() {
            $('#certPreviewModal').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // ESC 키로 닫기
        $(document).on('keydown.certModal', function(e) {
            if (e.keyCode === 27) {
                $('#certPreviewModal').fadeOut(300, function() {
                    $(this).remove();
                });
                $(document).off('keydown.certModal');
            }
        });
    });
    
    // 파일 업로드 미리보기
    function handleFileUpload(input, previewContainer) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            if (file.type.startsWith('image/')) {
                $(previewContainer).html(`<img src="${e.target.result}" alt="미리보기">`);
            } else {
                const fileName = file.name;
                const fileSize = (file.size / 1024).toFixed(2);
                $(previewContainer).html(`
                    <div class="file-info">
                        <i class="fas fa-file"></i>
                        <span>${fileName} (${fileSize}KB)</span>
                    </div>
                `);
            }
        };
        reader.readAsDataURL(file);
    }

    // 프로필 사진 업로드
    $('#profile_photo').on('change', function() {
        handleFileUpload(this, '#profile-photo-preview');
    });

    // 자격증 파일 업로드
    $('.cert-file-upload').on('change', function() {
        const previewId = $(this).data('preview');
        handleFileUpload(this, `#${previewId}`);
    });

    // 교육 파일 업로드
    $('.edu-file-upload').on('change', function() {
        const previewId = $(this).data('preview');
        handleFileUpload(this, `#${previewId}`);
    });

    // 활동 파일 업로드
    $('.activity-file-upload').on('change', function() {
        const previewId = $(this).data('preview');
        handleFileUpload(this, `#${previewId}`);
    });

    // 관리자 폼 제출
    $('.admin-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotice('success', '저장되었습니다.');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    showNotice('error', response.message || '저장 중 오류가 발생했습니다.');
                }
            },
            error: function() {
                showNotice('error', '서버 오류가 발생했습니다.');
            }
        });
    });

    // 알림 메시지 표시
    function showNotice(type, message) {
        const notice = $(`
            <div class="notice notice-${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `);

        $('.admin-container').prepend(notice);
        setTimeout(() => {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
});