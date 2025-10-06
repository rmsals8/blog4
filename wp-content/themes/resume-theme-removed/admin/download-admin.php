<?php
/**
 * 서류 일괄 다운로드 페이지
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 서류 다운로드 페이지 함수
function resume_download_page() {
    // 헤더 출력
    resume_admin_header('서류 일괄 다운로드');
    ?>
    
    <div class="download-admin-container">
        <!-- 서류 일괄 다운로드 섹션 -->
        <div class="documents-download-section">
            <h3>전체 이력서 서류 다운로드</h3>
            <p>모든 섹션의 서류를 폴더별로 정리하여 ZIP 파일로 다운로드할 수 있습니다.</p>
            
            <div class="download-info-box" style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h4>다운로드 포함 항목:</h4>
                <ul>
                    <li><strong>주요활동 및 사회경험</strong> - 경력증명서 파일들</li>
                    <li><strong>학력</strong> - 졸업/재학증명서, 성적증명서 파일들</li>
                    <li><strong>자격증</strong> - 자격증 파일들</li>
                    <li><strong>교육 내용</strong> - 수료증 파일들</li>
                </ul>
                <p><em>※ 각 섹션별로 폴더가 생성되며, 파일명은 한글로 정리됩니다.</em></p>
            </div>
            
            <p class="submit">
                <button type="button" id="download-documents-btn" class="button-primary" style="font-size: 16px; padding: 10px 20px;">
                    <i class="fas fa-download"></i> 전체 서류 다운로드
                </button>
                <span id="download-status" style="margin-left: 15px; font-weight: bold;"></span>
            </p>
            
            <div class="download-notes" style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 3px;">
                <h4>주의사항:</h4>
                <ul>
                    <li>서류 파일이 많을 경우 다운로드 준비에 시간이 걸릴 수 있습니다.</li>
                    <li>업로드된 서류가 없는 섹션은 ZIP 파일에 포함되지 않습니다.</li>
                    <li>파일명에 특수문자가 포함된 경우 자동으로 정리됩니다.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#download-documents-btn').on('click', function() {
            var $button = $(this);
            var $status = $('#download-status');
            
            // 버튼 비활성화 및 로딩 표시
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 서류 준비 중...');
            $status.html('');
            
            // AJAX 요청
            $.post(ajaxurl, {
                action: 'resume_download_documents',
                _ajax_nonce: '<?php echo wp_create_nonce('resume_download_documents'); ?>'
            }, function(response) {
                if (response.success) {
                    $status.html('<span style="color: green;"><i class="fas fa-check"></i> ' + response.data.file_count + '개 파일 준비 완료!</span>');
                    
                    // 다운로드 시작
                    var link = document.createElement('a');
                    link.href = response.data.download_url;
                    link.download = 'resume_documents.zip';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // 3초 후 상태 메시지 지우기
                    setTimeout(function() {
                        $status.html('');
                    }, 3000);
                } else {
                    $status.html('<span style="color: red;"><i class="fas fa-times"></i> 오류: ' + response.data + '</span>');
                }
            }).fail(function() {
                $status.html('<span style="color: red;"><i class="fas fa-times"></i> 서버 오류가 발생했습니다.</span>');
            }).always(function() {
                // 버튼 다시 활성화
                $button.prop('disabled', false).html('<i class="fas fa-download"></i> 전체 서류 다운로드');
            });
        });
    });
    </script>
    
    <?php
}
?>