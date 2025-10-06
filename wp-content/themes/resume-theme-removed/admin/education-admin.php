<?php
/**
 * 학력 관리 페이지 (파일 업로드 기능 포함)
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 학력 관리 페이지 함수
function resume_education_page() {
    global $wpdb;
    
    // 폼 제출 처리
    if (isset($_POST['submit_education'])) {
        $education_data = array(
            'school' => resume_sanitize_data($_POST['school']),
            'degree' => resume_sanitize_data($_POST['degree']),
            'field' => resume_sanitize_data($_POST['field']),
            'grade' => resume_sanitize_data($_POST['grade']),
            'total_credits' => resume_sanitize_data($_POST['total_credits']),
            'start_date' => resume_sanitize_data($_POST['start_date'], 'date'),
            'end_date' => resume_sanitize_data($_POST['end_date'], 'date'),
            'status' => resume_sanitize_data($_POST['status']),
            'description' => resume_sanitize_data($_POST['description'], 'textarea')
        );
        
        // 파일 업로드 처리 - 재학/졸업증명서
        if (!empty($_FILES['certificate_file']['name'])) {
            $upload_result = resume_handle_file_upload($_FILES['certificate_file']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $type = 'error';
            } else {
                $education_data['certificate_file'] = $upload_result['url'];
            }
        }
        
        // 파일 업로드 처리 - 성적증명서
        if (!empty($_FILES['transcript_file']['name'])) {
            $upload_result = resume_handle_file_upload($_FILES['transcript_file']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $type = 'error';
            } else {
                $education_data['transcript_file'] = $upload_result['url'];
            }
        }
        
        if (!isset($message)) {
            if (isset($_POST['education_id']) && !empty($_POST['education_id'])) {
                // 수정
                $result = $wpdb->update(
                    $wpdb->prefix . 'resume_education',
                    $education_data,
                    array('id' => intval($_POST['education_id']))
                );
                $message = $result !== false ? '학력이 수정되었습니다.' : '수정에 실패했습니다.';
            } else {
                // 새로 추가
                $result = $wpdb->insert($wpdb->prefix . 'resume_education', $education_data);
                $message = $result !== false ? '학력이 추가되었습니다.' : '추가에 실패했습니다.';
            }
            $type = $result !== false ? 'success' : 'error';
        }
        
        // 리다이렉트
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-education'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 삭제 처리
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $result = $wpdb->delete($wpdb->prefix . 'resume_education', array('id' => $id));
        
        $message = $result !== false ? '학력이 삭제되었습니다.' : '삭제에 실패했습니다.';
        $type = $result !== false ? 'success' : 'error';
        
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-education'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 수정할 학력 데이터 가져오기
    $edit_education = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_education = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}resume_education WHERE id = %d", intval($_GET['id'])));
    }
    
    // 학력 목록 가져오기
    $educations = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_education ORDER BY start_date DESC");
    
    // 헤더 출력
    resume_admin_header('학력 관리');
    ?>
    
    <div class="education-admin-container">
        <!-- 학력 추가/수정 폼 -->
        <div class="education-form-section">
            <h3><?php echo $edit_education ? '학력 수정' : '새 학력 추가'; ?></h3>
            <form method="post" enctype="multipart/form-data" class="education-form">
                <?php if ($edit_education): ?>
                    <input type="hidden" name="education_id" value="<?php echo esc_attr($edit_education->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="school">학교명 *</label></th>
                        <td>
                            <input type="text" id="school" name="school" class="regular-text" 
                                   value="<?php echo $edit_education ? esc_attr($edit_education->school) : ''; ?>" required>
                            <p class="description">예: 울산대학교, 서울대학교, 한국방송통신대학교</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="degree">학위</label></th>
                        <td>
                            <select id="degree" name="degree">
                                <option value="">선택하세요</option>
                                <option value="학사" <?php echo ($edit_education && $edit_education->degree === '학사') ? 'selected' : ''; ?>>학사</option>
                                <option value="석사" <?php echo ($edit_education && $edit_education->degree === '석사') ? 'selected' : ''; ?>>석사</option>
                                <option value="박사" <?php echo ($edit_education && $edit_education->degree === '박사') ? 'selected' : ''; ?>>박사</option>
                                <option value="전문학사" <?php echo ($edit_education && $edit_education->degree === '전문학사') ? 'selected' : ''; ?>>전문학사</option>
                                <option value="수료" <?php echo ($edit_education && $edit_education->degree === '수료') ? 'selected' : ''; ?>>수료</option>
                                <option value="기타" <?php echo ($edit_education && $edit_education->degree === '기타') ? 'selected' : ''; ?>>기타</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="field">전공 *</label></th>
                        <td>
                            <input type="text" id="field" name="field" class="regular-text" 
                                   value="<?php echo $edit_education ? esc_attr($edit_education->field) : ''; ?>" required>
                            <p class="description">예: IT 융합학부 AI 융합전공, 컴퓨터공학과</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="grade">학점/성적</label></th>
                        <td>
                            <input type="text" id="grade" name="grade" class="regular-text" 
                                   value="<?php echo $edit_education ? esc_attr($edit_education->grade) : ''; ?>">
                            <p class="description">예: 3.8/4.5, 4.2/4.5, 상위 10%</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="total_credits">총취득학점</label></th>
                        <td>
                            <input type="text" id="total_credits" name="total_credits" class="regular-text" 
                                   value="<?php echo $edit_education ? esc_attr($edit_education->total_credits) : ''; ?>">
                            <p class="description">예: 130학점, 140학점</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="start_date">입학일 *</label></th>
                        <td>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo $edit_education ? esc_attr($edit_education->start_date) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="end_date">졸업일(예정일)</label></th>
                        <td>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo $edit_education ? esc_attr($edit_education->end_date) : ''; ?>">
                            <p class="description">재학 중이면 비워두거나 졸업예정일을 입력하세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="status">상태</label></th>
                        <td>
                            <select id="status" name="status">
                                <option value="current" <?php echo ($edit_education && $edit_education->status === 'current') ? 'selected' : ''; ?>>재학중</option>
                                <option value="graduated" <?php echo ($edit_education && $edit_education->status === 'graduated') ? 'selected' : ''; ?>>졸업</option>
                                <option value="leave" <?php echo ($edit_education && $edit_education->status === 'leave') ? 'selected' : ''; ?>>휴학중</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description">추가 설명</label></th>
                        <td>
                            <textarea id="description" name="description" rows="3" class="large-text"><?php echo $edit_education ? esc_textarea($edit_education->description) : ''; ?></textarea>
                            <p class="description">주요 과목, 논문 제목, 특별 활동 등</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="certificate_file">졸업/재학증명서</label></th>
                        <td>
                            <input type="file" id="certificate_file" name="certificate_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <p class="description">졸업증명서 또는 재학증명서 업로드 (최대 5MB)</p>
                            
                            <?php if ($edit_education && $edit_education->certificate_file): ?>
                                <div class="current-file">
                                    <h4>현재 파일:</h4>
                                    <div class="file-preview">
                                        <?php echo resume_generate_file_preview($edit_education->certificate_file); ?>
                                    </div>
                                    <p><a href="<?php echo esc_url($edit_education->certificate_file); ?>" target="_blank">파일 다운로드</a></p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="transcript_file">성적증명서</label></th>
                        <td>
                            <input type="file" id="transcript_file" name="transcript_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <p class="description">성적증명서 업로드 (최대 5MB)</p>
                            
                            <?php if ($edit_education && $edit_education->transcript_file): ?>
                                <div class="current-file">
                                    <h4>현재 파일:</h4>
                                    <div class="file-preview">
                                        <?php echo resume_generate_file_preview($edit_education->transcript_file); ?>
                                    </div>
                                    <p><a href="<?php echo esc_url($edit_education->transcript_file); ?>" target="_blank">파일 다운로드</a></p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit_education" class="button-primary" 
                           value="<?php echo $edit_education ? '학력 수정' : '학력 추가'; ?>">
                    <?php if ($edit_education): ?>
                        <a href="<?php echo admin_url('admin.php?page=resume-education'); ?>" class="button-secondary">취소</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- 학력 목록 -->
        <div class="education-list-section">
            <h3>등록된 학력</h3>
            
            <?php if (empty($educations)): ?>
                <p>등록된 학력이 없습니다.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>학교명</th>
                            <th>학위/전공</th>
                            <th>기간</th>
                            <th>상태</th>
                            <th>파일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($educations as $education): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($education->school); ?></strong>
                                    <?php if ($education->grade): ?>
                                        <br><small>학점/성적: <?php echo esc_html($education->grade); ?></small>
                                    <?php endif; ?>
                                    <?php if (isset($education->total_credits) && $education->total_credits): ?>
                                        <br><small>총취득학점: <?php echo esc_html($education->total_credits); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($education->degree); ?><br>
                                    <small><?php echo esc_html($education->field); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    if ($education->start_date) {
                                        echo esc_html(date('Y.m', strtotime($education->start_date)));
                                        if ($education->end_date) {
                                            echo ' ~ ' . esc_html(date('Y.m', strtotime($education->end_date)));
                                        } else {
                                            echo ' ~ 현재';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $status_labels = array(
                                        'current' => '재학중',
                                        'graduated' => '졸업',
                                        'leave' => '휴학중'
                                    );
                                    echo esc_html($status_labels[$education->status] ?? $education->status); 
                                    ?>
                                </td>
                                <td>
                                    <?php if ($education->certificate_file): ?>
                                        <a href="<?php echo esc_url($education->certificate_file); ?>" target="_blank" class="button-secondary" style="display:block; margin-bottom:5px;">
                                            <i class="fas fa-graduation-cap"></i> 졸업/재학증명서 보기
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isset($education->transcript_file) && $education->transcript_file): ?>
                                        <a href="<?php echo esc_url($education->transcript_file); ?>" target="_blank" class="button-secondary" style="display:block;">
                                            <i class="fas fa-file-alt"></i> 성적증명서 보기
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$education->certificate_file && (!isset($education->transcript_file) || !$education->transcript_file)): ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=resume-education&action=edit&id=' . $education->id); ?>" 
                                       class="button-secondary">수정</a>
                                    <a href="<?php echo admin_url('admin.php?page=resume-education&action=delete&id=' . $education->id); ?>" 
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