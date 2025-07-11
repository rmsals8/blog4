<?php
/**
 * 활동 및 경험 관리 페이지 (파일 업로드 기능 포함)
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 활동 및 경험 관리 페이지 함수
function resume_activities_page() {
    global $wpdb;
    
    // 폼 제출 처리
    if (isset($_POST['submit_activity'])) {
        $activity_data = array(
            'title' => resume_sanitize_data($_POST['title']),
            'organization' => resume_sanitize_data($_POST['organization']),
            'role' => resume_sanitize_data($_POST['role']),
            'start_date' => resume_sanitize_data($_POST['start_date'], 'date'),
            'end_date' => resume_sanitize_data($_POST['end_date'], 'date'),
            'description' => resume_sanitize_data($_POST['description'], 'textarea'),
            'achievements' => resume_sanitize_data($_POST['achievements'], 'textarea')
        );
        
        // 파일 업로드 처리
        if (!empty($_FILES['certificate_file']['name'])) {
            $upload_result = resume_handle_file_upload($_FILES['certificate_file']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $type = 'error';
            } else {
                $activity_data['certificate_file'] = $upload_result['url'];
            }
        }
        
        if (!isset($message)) {
            if (isset($_POST['activity_id']) && !empty($_POST['activity_id'])) {
                // 수정
                $result = $wpdb->update(
                    $wpdb->prefix . 'resume_activities',
                    $activity_data,
                    array('id' => intval($_POST['activity_id']))
                );
                $message = $result !== false ? '활동이 수정되었습니다.' : '수정에 실패했습니다.';
            } else {
                // 새로 추가
                $result = $wpdb->insert($wpdb->prefix . 'resume_activities', $activity_data);
                $message = $result !== false ? '활동이 추가되었습니다.' : '추가에 실패했습니다.';
            }
            $type = $result !== false ? 'success' : 'error';
        }
        
        // 리다이렉트
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-activities'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 삭제 처리
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $result = $wpdb->delete($wpdb->prefix . 'resume_activities', array('id' => $id));
        
        $message = $result !== false ? '활동이 삭제되었습니다.' : '삭제에 실패했습니다.';
        $type = $result !== false ? 'success' : 'error';
        
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-activities'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 수정할 활동 데이터 가져오기
    $edit_activity = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_activity = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}resume_activities WHERE id = %d", intval($_GET['id'])));
    }
    
    // 활동 목록 가져오기
    $activities = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_activities ORDER BY start_date DESC");
    
    // 헤더 출력
    resume_admin_header('활동 및 경험 관리');
    ?>
    
    <div class="activity-admin-container">
        <!-- 활동 추가/수정 폼 -->
        <div class="activity-form-section">
            <h3><?php echo $edit_activity ? '활동 수정' : '새 활동 추가'; ?></h3>
            <form method="post" enctype="multipart/form-data" class="activity-form">
                <?php if ($edit_activity): ?>
                    <input type="hidden" name="activity_id" value="<?php echo esc_attr($edit_activity->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="title">활동명 *</label></th>
                        <td>
                            <input type="text" id="title" name="title" class="regular-text" 
                                   value="<?php echo $edit_activity ? esc_attr($edit_activity->title) : ''; ?>" required>
                            <p class="description">예: ㈜에스이에스케이 시스템 운영 인턴, 대학생 프로그래밍 대회 참가</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="organization">기관/회사명</label></th>
                        <td>
                            <input type="text" id="organization" name="organization" class="regular-text" 
                                   value="<?php echo $edit_activity ? esc_attr($edit_activity->organization) : ''; ?>">
                            <p class="description">예: 현대모비스 HAIMS 운영팀</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="role">역할/직책</label></th>
                        <td>
                            <input type="text" id="role" name="role" class="regular-text" 
                                   value="<?php echo $edit_activity ? esc_attr($edit_activity->role) : ''; ?>">
                            <p class="description">예: 시스템 운영 인턴, 팀장, 참가자</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_date">시작일 *</label></th>
                        <td>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo $edit_activity ? esc_attr($edit_activity->start_date) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end_date">종료일</label></th>
                        <td>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo $edit_activity ? esc_attr($edit_activity->end_date) : ''; ?>">
                            <p class="description">현재 진행 중인 활동은 비워두세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description">활동 내용</label></th>
                        <td>
                            <textarea id="description" name="description" rows="4" class="large-text"><?php echo $edit_activity ? esc_textarea($edit_activity->description) : ''; ?></textarea>
                            <p class="description">주요 업무, 담당 역할, 참여 내용 등을 구체적으로 기술하세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="achievements">성과/배운점</label></th>
                        <td>
                            <textarea id="achievements" name="achievements" rows="3" class="large-text"><?php echo $edit_activity ? esc_textarea($edit_activity->achievements) : ''; ?></textarea>
                            <p class="description">활동을 통해 얻은 성과, 배운 점, 인사이트 등을 기술하세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="certificate_file">관련 파일</label></th>
                        <td>
                            <input type="file" id="certificate_file" name="certificate_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <p class="description">재직증명서, 참가증명서, 수료증, 포상장 등 관련 파일 업로드 (최대 5MB)</p>
                            
                            <?php if ($edit_activity && $edit_activity->certificate_file): ?>
                                <div class="current-file">
                                    <h4>현재 파일:</h4>
                                    <div class="file-preview">
                                        <?php echo resume_generate_file_preview($edit_activity->certificate_file); ?>
                                    </div>
                                    <p><a href="<?php echo esc_url($edit_activity->certificate_file); ?>" target="_blank">파일 다운로드</a></p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit_activity" class="button-primary" 
                           value="<?php echo $edit_activity ? '활동 수정' : '활동 추가'; ?>">
                    <?php if ($edit_activity): ?>
                        <a href="<?php echo admin_url('admin.php?page=resume-activities'); ?>" class="button-secondary">취소</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- 활동 목록 -->
        <div class="activity-list-section">
            <h3>등록된 활동 및 경험</h3>
            
            <?php if (empty($activities)): ?>
                <p>등록된 활동이 없습니다.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>활동명</th>
                            <th>기관/회사</th>
                            <th>역할</th>
                            <th>기간</th>
                            <th>파일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($activity->title); ?></strong>
                                    <?php if ($activity->description): ?>
                                        <br><small><?php echo esc_html(wp_trim_words($activity->description, 10)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($activity->organization); ?></td>
                                <td><?php echo esc_html($activity->role); ?></td>
                                <td>
                                    <?php 
                                    if ($activity->start_date) {
                                        echo esc_html(date('Y.m', strtotime($activity->start_date)));
                                        if ($activity->end_date) {
                                            echo ' ~ ' . esc_html(date('Y.m', strtotime($activity->end_date)));
                                        } else {
                                            echo ' ~ 현재';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($activity->certificate_file): ?>
                                        <a href="<?php echo esc_url($activity->certificate_file); ?>" target="_blank" class="button-secondary">
                                            <i class="fas fa-file-alt"></i> 보기
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=resume-activities&action=edit&id=' . $activity->id); ?>" 
                                       class="button-secondary">수정</a>
                                    <a href="<?php echo admin_url('admin.php?page=resume-activities&action=delete&id=' . $activity->id); ?>" 
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