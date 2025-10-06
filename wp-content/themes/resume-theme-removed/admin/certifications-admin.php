<?php
/**
 * 자격증 관리 페이지
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 자격증 관리 페이지 함수
function resume_certifications_page() {
    global $wpdb;
    
    // 삭제 처리 (출력 전에 먼저 처리)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $result = $wpdb->delete($wpdb->prefix . 'resume_certifications', array('id' => $id));
        
        $message = $result !== false ? '자격증이 삭제되었습니다.' : '삭제에 실패했습니다.';
        $type = $result !== false ? 'success' : 'error';
        
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-certifications'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 폼 제출 처리 (출력 전에 먼저 처리)
    if (isset($_POST['submit_certification'])) {
        $certification_data = array(
            'name' => resume_sanitize_data($_POST['name']),
            'organization' => resume_sanitize_data($_POST['organization']),
            'certificate_number' => resume_sanitize_data($_POST['certificate_number']),
            'issue_date' => resume_sanitize_data($_POST['issue_date'], 'date'),
            'expiry_date' => resume_sanitize_data($_POST['expiry_date'], 'date'),
            'score' => resume_sanitize_data($_POST['score']),
            'level' => resume_sanitize_data($_POST['level']),
            'description' => resume_sanitize_data($_POST['description'], 'textarea')
        );
        
        // 파일 업로드 처리
        if (!empty($_FILES['certificate_file']['name'])) {
            $upload_result = resume_handle_file_upload($_FILES['certificate_file']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $type = 'error';
            } else {
                $certification_data['certificate_file'] = $upload_result['url'];
            }
        }
        
        if (!isset($message)) {
            if (isset($_POST['certification_id']) && !empty($_POST['certification_id'])) {
                // 수정
                $result = $wpdb->update(
                    $wpdb->prefix . 'resume_certifications',
                    $certification_data,
                    array('id' => intval($_POST['certification_id']))
                );
                $message = $result !== false ? '자격증이 수정되었습니다.' : '수정에 실패했습니다.';
            } else {
                // 새로 추가
                $result = $wpdb->insert($wpdb->prefix . 'resume_certifications', $certification_data);
                $message = $result !== false ? '자격증이 추가되었습니다.' : '추가에 실패했습니다.';
            }
            $type = $result !== false ? 'success' : 'error';
        }
        
        // 리다이렉트 (출력 전에 처리)
        $redirect_url = add_query_arg(array('message' => urlencode($message), 'type' => $type), admin_url('admin.php?page=resume-certifications'));
        wp_redirect($redirect_url);
        exit;
    }
    
    // 수정할 자격증 데이터 가져오기
    $edit_certification = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_certification = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}resume_certifications WHERE id = %d", intval($_GET['id'])));
    }
    
    // 자격증 목록 가져오기
    $certifications = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_certifications ORDER BY issue_date DESC");
    
    // 헤더 출력
    resume_admin_header('자격증 관리');
    ?>
    
    <div class="certification-admin-container">
        <!-- 자격증 추가/수정 폼 -->
        <div class="certification-form-section">
            <h3><?php echo $edit_certification ? '자격증 수정' : '새 자격증 추가'; ?></h3>
            <form method="post" enctype="multipart/form-data" class="certification-form">
                <?php if ($edit_certification): ?>
                    <input type="hidden" name="certification_id" value="<?php echo esc_attr($edit_certification->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="name">자격증명 *</label></th>
                        <td>
                            <input type="text" id="name" name="name" class="regular-text" 
                                   value="<?php echo $edit_certification ? esc_attr($edit_certification->name) : ''; ?>" required>
                            <p class="description">예: TOEIC, 정보처리기사, SQLD</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="organization">발급기관</label></th>
                        <td>
                            <input type="text" id="organization" name="organization" class="regular-text" 
                                   value="<?php echo $edit_certification ? esc_attr($edit_certification->organization) : ''; ?>">
                            <p class="description">예: ETS, 한국산업인력공단</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="certificate_number">자격증 번호</label></th>
                        <td>
                            <input type="text" id="certificate_number" name="certificate_number" class="regular-text" 
                                   value="<?php echo $edit_certification ? esc_attr($edit_certification->certificate_number) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="issue_date">취득일</label></th>
                        <td>
                            <input type="date" id="issue_date" name="issue_date" 
                                   value="<?php echo $edit_certification ? esc_attr($edit_certification->issue_date) : ''; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="expiry_date">만료일</label></th>
                        <td>
                            <input type="date" id="expiry_date" name="expiry_date" 
                                   value="<?php echo $edit_certification ? esc_attr($edit_certification->expiry_date) : ''; ?>">
                            <p class="description">평생 유효한 자격증은 비워두세요</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="score">점수/등급</label></th>
                        <td>
                            <input type="text" id="score" name="score" class="regular-text" 
                                   value="<?php echo $edit_certification ? esc_attr($edit_certification->score) : ''; ?>">
                            <p class="description">예: 860점, IH(Intermediate High), 필기 합격</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="level">난이도/레벨</label></th>
                        <td>
                            <select id="level" name="level">
                                <option value="">선택하세요</option>
                                <option value="국가기술자격" <?php echo ($edit_certification && $edit_certification->level === '국가기술자격') ? 'selected' : ''; ?>>국가기술자격</option>
                                <option value="국가전문자격" <?php echo ($edit_certification && $edit_certification->level === '국가전문자격') ? 'selected' : ''; ?>>국가전문자격</option>
                                <option value="민간자격" <?php echo ($edit_certification && $edit_certification->level === '민간자격') ? 'selected' : ''; ?>>민간자격</option>
                                <option value="국제자격" <?php echo ($edit_certification && $edit_certification->level === '국제자격') ? 'selected' : ''; ?>>국제자격</option>
                                <option value="어학" <?php echo ($edit_certification && $edit_certification->level === '어학') ? 'selected' : ''; ?>>어학</option>
                                <option value="기타" <?php echo ($edit_certification && $edit_certification->level === '기타') ? 'selected' : ''; ?>>기타</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description">추가 설명</label></th>
                        <td>
                            <textarea id="description" name="description" rows="3" class="large-text"><?php echo $edit_certification ? esc_textarea($edit_certification->description) : ''; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="certificate_file">자격증 파일</label></th>
                        <td>
                            <input type="file" id="certificate_file" name="certificate_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <p class="description">이미지, PDF, Word 파일 업로드 가능 (최대 5MB)</p>
                            
                            <?php if ($edit_certification && $edit_certification->certificate_file): ?>
                                <div class="current-file">
                                    <h4>현재 파일:</h4>
                                    <div class="file-preview">
                                        <?php echo resume_generate_file_preview($edit_certification->certificate_file); ?>
                                    </div>
                                    <p><a href="<?php echo esc_url($edit_certification->certificate_file); ?>" target="_blank">파일 다운로드</a></p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit_certification" class="button-primary" 
                           value="<?php echo $edit_certification ? '자격증 수정' : '자격증 추가'; ?>">
                    <?php if ($edit_certification): ?>
                        <a href="<?php echo admin_url('admin.php?page=resume-certifications'); ?>" class="button-secondary">취소</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- 자격증 목록 -->
        <div class="certification-list-section">
            <h3>등록된 자격증</h3>
            
            <?php if (empty($certifications)): ?>
                <p>등록된 자격증이 없습니다.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>자격증명</th>
                            <th>발급기관</th>
                            <th>점수/등급</th>
                            <th>취득일</th>
                            <th>파일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certifications as $cert): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($cert->name); ?></strong>
                                    <?php if ($cert->certificate_number): ?>
                                        <br><small><?php echo esc_html($cert->certificate_number); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($cert->organization); ?></td>
                                <td><?php echo esc_html($cert->score); ?></td>
                                <td><?php echo $cert->issue_date ? esc_html(date('Y.m.d', strtotime($cert->issue_date))) : '-'; ?></td>
                                <td>
                                    <?php if ($cert->certificate_file): ?>
                                        <a href="<?php echo esc_url($cert->certificate_file); ?>" target="_blank" class="button-secondary">
                                            <i class="fas fa-file"></i> 보기
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=resume-certifications&action=edit&id=' . $cert->id); ?>" 
                                       class="button-secondary">수정</a>
                                    <a href="<?php echo admin_url('admin.php?page=resume-certifications&action=delete&id=' . $cert->id); ?>" 
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