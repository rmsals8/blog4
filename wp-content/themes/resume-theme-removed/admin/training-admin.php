<?php
/**
 * 교육 내용 관리 페이지
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 교육 내용 관리 페이지 함수
function resume_training_page() {
    global $wpdb;
    
    // 폼 제출 처리
    if (isset($_POST['submit_training'])) {
        $training_data = array(
            'title' => resume_sanitize_data($_POST['title']),
            'organization' => resume_sanitize_data($_POST['organization']),
            'instructor' => resume_sanitize_data($_POST['instructor']),
            'start_date' => resume_sanitize_data($_POST['start_date'], 'date'),
            'end_date' => resume_sanitize_data($_POST['end_date'], 'date'),
            'duration' => resume_sanitize_data($_POST['duration']),
            'description' => resume_sanitize_data($_POST['description'], 'textarea'),
            'skills_learned' => resume_sanitize_data($_POST['skills_learned'], 'textarea')
        );
        
        // 파일 업로드 처리
        if (!empty($_FILES['certificate_file']['name'])) {
            $upload_result = resume_handle_file_upload($_FILES['certificate_file']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $type = 'error';
            } else {
                $training_data['certificate_file'] = $upload_result['url'];
            }
        }
        
        if (!isset($message)) {
            if (isset($_POST['training_id']) && !empty($_POST['training_id'])) {
                // 수정
                $result = $wpdb->update(
                    $wpdb->prefix . 'resume_training',
                    $training_data,
                    array('id' => intval($_POST['training_id']))
                );
                $message = $result !== false ? '교육 내용이 수정되었습니다.' : '수정에 실패했습니다.';
            } else {
                // 새로 추가
                $result = $wpdb->insert($wpdb->prefix . 'resume_training', $training_data);
                $message = $result !== false ? '교육 내용이 추가되었습니다.' : '추가에 실패했습니다.';
            }
            $type = $result !== false ? 'success' : 'error';
        }
        
        // 리다이렉트
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-training'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 삭제 처리
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $result = $wpdb->delete($wpdb->prefix . 'resume_training', array('id' => $id));
        
        $message = $result !== false ? '교육 내용이 삭제되었습니다.' : '삭제에 실패했습니다.';
        $type = $result !== false ? 'success' : 'error';
        
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-training'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 수정할 교육 내용 데이터 가져오기
    $edit_training = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_training = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}resume_training WHERE id = %d", intval($_GET['id'])));
    }
    
    // 교육 내용 목록 가져오기
    $trainings = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_training ORDER BY start_date DESC");
    
    // 헤더 출력
    resume_admin_header('교육 내용 관리');
    ?>
    
    <div class="training-admin-container">
        <!-- 교육 내용 추가/수정 폼 -->
        <div class="training-form-section">
            <h3><?php echo $edit_training ? '교육 내용 수정' : '새 교육 내용 추가'; ?></h3>
            <form method="post" enctype="multipart/form-data" class="training-form">
                <?php if ($edit_training): ?>
                    <input type="hidden" name="training_id" value="<?php echo esc_attr($edit_training->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="title">교육명 *</label></th>
                        <td>
                            <input type="text" id="title" name="title" class="regular-text" 
                                   value="<?php echo $edit_training ? esc_attr($edit_training->title) : ''; ?>" required>
                            <p class="description">예: Future ICT Global Challenge Program (AI Training)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="organization">교육기관 *</label></th>
                        <td>
                            <input type="text" id="organization" name="organization" class="regular-text" 
                                   value="<?php echo $edit_training ? esc_attr($edit_training->organization) : ''; ?>" required>
                            <p class="description">예: University of Waterloo, 삼성청년SW아카데미</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="instructor">강사/담당자</label></th>
                        <td>
                            <input type="text" id="instructor" name="instructor" class="regular-text" 
                                   value="<?php echo $edit_training ? esc_attr($edit_training->instructor) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_date">시작일 *</label></th>
                        <td>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo $edit_training ? esc_attr($edit_training->start_date) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end_date">종료일</label></th>
                        <td>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo $edit_training ? esc_attr($edit_training->end_date) : ''; ?>">
                            <p class="description">진행 중인 교육은 비워두세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="duration">교육 기간</label></th>
                        <td>
                            <input type="text" id="duration" name="duration" class="regular-text" 
                                   value="<?php echo $edit_training ? esc_attr($edit_training->duration) : ''; ?>">
                            <p class="description">예: 4주, 320시간, 6개월</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description">교육 내용</label></th>
                        <td>
                            <textarea id="description" name="description" rows="4" class="large-text"><?php echo $edit_training ? esc_textarea($edit_training->description) : ''; ?></textarea>
                            <p class="description">교육의 주요 내용, 목표, 특징 등을 설명하세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="skills_learned">습득 기술/지식</label></th>
                        <td>
                            <textarea id="skills_learned" name="skills_learned" rows="3" class="large-text"><?php echo $edit_training ? esc_textarea($edit_training->skills_learned) : ''; ?></textarea>
                            <p class="description">교육을 통해 습득한 기술, 지식, 도구 등을 기재하세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="certificate_file">수료증/증명서</label></th>
                        <td>
                            <input type="file" id="certificate_file" name="certificate_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <p class="description">수료증, 참가증명서 등 파일 업로드 (최대 5MB)</p>
                            
                            <?php if ($edit_training && $edit_training->certificate_file): ?>
                                <div class="current-file">
                                    <h4>현재 파일:</h4>
                                    <div class="file-preview">
                                        <?php echo resume_generate_file_preview($edit_training->certificate_file); ?>
                                    </div>
                                    <p><a href="<?php echo esc_url($edit_training->certificate_file); ?>" target="_blank">파일 다운로드</a></p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit_training" class="button-primary" 
                           value="<?php echo $edit_training ? '교육 내용 수정' : '교육 내용 추가'; ?>">
                    <?php if ($edit_training): ?>
                        <a href="<?php echo admin_url('admin.php?page=resume-training'); ?>" class="button-secondary">취소</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- 교육 내용 목록 -->
        <div class="training-list-section">
            <h3>등록된 교육 내용</h3>
            
            <?php if (empty($trainings)): ?>
                <p>등록된 교육 내용이 없습니다.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>교육명</th>
                            <th>교육기관</th>
                            <th>기간</th>
                            <th>수료증</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainings as $training): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($training->title); ?></strong>
                                    <?php if ($training->duration): ?>
                                        <br><small>기간: <?php echo esc_html($training->duration); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($training->organization); ?>
                                    <?php if ($training->instructor): ?>
                                        <br><small>강사: <?php echo esc_html($training->instructor); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($training->start_date) {
                                        echo esc_html(date('Y.m.d', strtotime($training->start_date)));
                                        if ($training->end_date) {
                                            echo ' ~ ' . esc_html(date('Y.m.d', strtotime($training->end_date)));
                                        } else {
                                            echo ' ~ 진행중';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($training->certificate_file): ?>
                                        <a href="<?php echo esc_url($training->certificate_file); ?>" target="_blank" class="button-secondary">
                                            <i class="fas fa-certificate"></i> 보기
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=resume-training&action=edit&id=' . $training->id); ?>" 
                                       class="button-secondary">수정</a>
                                    <a href="<?php echo admin_url('admin.php?page=resume-training&action=delete&id=' . $training->id); ?>" 
                                       class="button-secondary" 
                                       onclick="return confirm('정말로 삭제하시겠습니까?')">삭제</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
}
?>