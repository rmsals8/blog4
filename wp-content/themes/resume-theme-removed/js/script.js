jQuery(document).ready(function($) {
    // 파일 업로드 핸들러
    function handleFileUpload(fileInput, callback) {
        var file = fileInput.files[0];
        if (!file) return;
        
        var formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'handle_file_upload');
        formData.append('nonce', ajax_object.nonce);
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    callback(response.data);
                } else {
                    alert('Upload failed: ' + response.data);
                }
            },
            error: function() {
                alert('Upload failed. Please try again.');
            }
        });
    }
    
    // 이미지 미리보기 생성
    function createImagePreview(fileUrl, filename) {
        var isImage = /\.(jpg|jpeg|png|gif)$/i.test(filename);
        var isPDF = /\.pdf$/i.test(filename);
        
        if (isImage) {
            return '<img src="' + fileUrl + '" class="cert-thumbnail" alt="Certificate">';
        } else if (isPDF) {
            return '<a href="' + fileUrl + '" target="_blank" class="cert-pdf-link">PDF 보기</a>';
        } else {
            return '<a href="' + fileUrl + '" target="_blank" class="attachment">첨부파일 보기</a>';
        }
    }
    
    // 파일 업로드 이벤트 리스너
    $('.file-upload-input').on('change', function() {
        var $this = $(this);
        var $preview = $this.closest('.file-upload-container').find('.file-preview');
        
        handleFileUpload(this, function(data) {
            var preview = createImagePreview(data.file_url, data.filename);
            $preview.html(preview).show();
            
            // 숨겨진 input에 파일 URL 저장
            var $hiddenInput = $this.closest('.file-upload-container').find('.file-url-input');
            if ($hiddenInput.length) {
                $hiddenInput.val(data.file_url);
            }
        });
    });
    
    // 폼 제출 시 AJAX 처리
    $('.resume-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var action = $(this).data('action');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: formData + '&action=' + action + '&nonce=' + ajax_object.nonce,
            success: function(response) {
                if (response.success) {
                    alert('Data saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // 삭제 버튼 이벤트
    $('.delete-btn').on('click', function() {
        if (confirm('Are you sure you want to delete this item?')) {
            var itemId = $(this).data('id');
            var itemType = $(this).data('type');
            
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_resume_item',
                    item_id: itemId,
                    item_type: itemType,
                    nonce: ajax_object.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
    
    // 파일 드래그 앤 드롭 기능
    $('.file-upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('.file-upload-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    $('.file-upload-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            var $fileInput = $(this).find('.file-upload-input')[0];
            $fileInput.files = files;
            $fileInput.dispatchEvent(new Event('change'));
        }
    });
    
    // 실시간 미리보기
    $('.live-preview').on('input', function() {
        var field = $(this).data('field');
        var value = $(this).val();
        $('.preview-' + field).text(value);
    });
    
    // 하이퍼링크 자동 감지
    function autoLinkify(text) {
        var urlRegex = /(https?:\/\/[^\s]+)/g;
        return text.replace(urlRegex, '<a href="$1" target="_blank">$1</a>');
    }
    
    // 텍스트 영역에서 URL 자동 링크화
    $('.auto-link').on('blur', function() {
        var $this = $(this);
        var text = $this.val();
        if (text.match(/(https?:\/\/[^\s]+)/g)) {
            var linkedText = autoLinkify(text);
            $this.siblings('.preview').html(linkedText);
        }
    });
    
    // 탭 기능
    $('.tab-nav').on('click', 'a', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        $('.tab-nav a').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-content').hide();
        $(target).show();
    });
    
    // 모달 기능
    $('.modal-trigger').on('click', function() {
        var modalId = $(this).data('modal');
        $('#' + modalId).fadeIn();
    });
    
    $('.modal-close, .modal-backdrop').on('click', function() {
        $('.modal').fadeOut();
    });
    
    // 스크롤 시 네비게이션 고정
    $(window).on('scroll', function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > 100) {
            $('.sticky-nav').addClass('fixed');
        } else {
            $('.sticky-nav').removeClass('fixed');
        }
    });
    
    // 부드러운 스크롤
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 500);
        }
    });
    
    // 폼 유효성 검사
    $('.validate-form').on('submit', function(e) {
        var isValid = true;
        
        $(this).find('.required').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // 이메일 유효성 검사
    $('.email-field').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('error');
            $(this).siblings('.error-message').text('Please enter a valid email address.');
        } else {
            $(this).removeClass('error');
            $(this).siblings('.error-message').text('');
        }
    });
    
    // URL 유효성 검사
    $('.url-field').on('blur', function() {
        var url = $(this).val();
        var urlRegex = /^https?:\/\/.+/;
        
        if (url && !urlRegex.test(url)) {
            $(this).addClass('error');
            $(this).siblings('.error-message').text('Please enter a valid URL starting with http:// or https://');
        } else {
            $(this).removeClass('error');
            $(this).siblings('.error-message').text('');
        }
    });
    
    // 자동 저장 기능
    var autoSaveTimer;
    $('.auto-save').on('input', function() {
        clearTimeout(autoSaveTimer);
        var $form = $(this).closest('form');
        
        autoSaveTimer = setTimeout(function() {
            var formData = $form.serialize();
            var action = $form.data('action');
            
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: formData + '&action=' + action + '&nonce=' + ajax_object.nonce,
                success: function(response) {
                    if (response.success) {
                        $('.auto-save-status').text('Auto-saved').show().fadeOut(2000);
                    }
                }
            });
        }, 2000);
    });
    
    // 프로필 이미지 크롭 기능 (선택적)
    $('.profile-image-upload').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-preview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // 키보드 단축키
    $(document).on('keydown', function(e) {
        // Ctrl+S로 저장
        if (e.ctrlKey && e.which == 83) {
            e.preventDefault();
            $('.save-btn').click();
        }
        
        // ESC로 모달 닫기
        if (e.which == 27) {
            $('.modal').fadeOut();
        }
    });
    
    // 다크 모드 토글 (선택적)
    $('.theme-toggle').on('click', function() {
        $('body').toggleClass('dark-mode');
        localStorage.setItem('theme', $('body').hasClass('dark-mode') ? 'dark' : 'light');
    });
    
    // 저장된 테마 로드
    if (localStorage.getItem('theme') === 'dark') {
        $('body').addClass('dark-mode');
    }
    
    // 애니메이션 효과
    function animateOnScroll() {
        $('.animate-on-scroll').each(function() {
            var elementTop = $(this).offset().top;
            var elementBottom = elementTop + $(this).outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();
            
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('animate');
            }
        });
    }
    
    $(window).on('scroll', animateOnScroll);
    animateOnScroll(); // 페이지 로드 시 실행
}); 