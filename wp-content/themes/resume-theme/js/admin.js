jQuery(document).ready(function($) {
    // 파일 업로드 미리보기
    function handleFilePreview(input, previewContainer) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                let previewContent = '';
                if (file.type.startsWith('image/')) {
                    previewContent = `<img src="${e.target.result}" alt="미리보기">`;
                } else {
                    previewContent = `
                        <div class="file-info">
                            <i class="fas fa-file"></i>
                            <span>${file.name} (${(file.size / 1024).toFixed(2)}KB)</span>
                        </div>
                    `;
                }
                $(previewContainer).html(previewContent);
            };
            
            reader.readAsDataURL(file);
        }
    }

    // 자격증 관리
    let certIndex = $('.certification-item').length;
    
    $('#add-certification').on('click', function() {
        const template = $('#cert-template').html();
        const newItem = template.replace(/{{index}}/g, certIndex);
        $('#certifications-container').append(newItem);
        certIndex++;
    });

    $(document).on('click', '.remove-cert', function() {
        $(this).closest('.certification-item').remove();
    });

    $(document).on('change', '.cert-file-input', function() {
        const previewId = $(this).siblings('.file-preview').attr('id');
        handleFilePreview(this, `#${previewId}`);
    });

    // 프로필 사진 업로드
    $('#profile_photo').on('change', function() {
        handleFilePreview(this, '.profile-photo-preview');
    });

    // 교육 이력 관리
    let eduIndex = $('.education-item').length;
    
    $('#add-education').on('click', function() {
        const template = $('#edu-template').html();
        const newItem = template.replace(/{{index}}/g, eduIndex);
        $('#education-container').append(newItem);
        eduIndex++;
    });

    $(document).on('click', '.remove-edu', function() {
        $(this).closest('.education-item').remove();
    });

    $(document).on('change', '.edu-file-input', function() {
        const previewId = $(this).siblings('.file-preview').attr('id');
        handleFilePreview(this, `#${previewId}`);
    });

    // 활동/경험 관리
    let activityIndex = $('.activity-item').length;
    
    $('#add-activity').on('click', function() {
        const template = $('#activity-template').html();
        const newItem = template.replace(/{{index}}/g, activityIndex);
        $('#activities-container').append(newItem);
        activityIndex++;
    });

    $(document).on('click', '.remove-activity', function() {
        $(this).closest('.activity-item').remove();
    });

    $(document).on('change', '.activity-file-input', function() {
        const previewId = $(this).siblings('.file-preview').attr('id');
        handleFilePreview(this, `#${previewId}`);
    });

    // 프로젝트 관리
    let projectIndex = $('.project-item').length;
    
    $('#add-project').on('click', function() {
        const template = $('#project-template').html();
        const newItem = template.replace(/{{index}}/g, projectIndex);
        $('#projects-container').append(newItem);
        projectIndex++;
    });

    $(document).on('click', '.remove-project', function() {
        $(this).closest('.project-item').remove();
    });

    // 알림 메시지 자동 숨김
    setTimeout(function() {
        $('.notice').fadeOut();
    }, 3000);
}); 