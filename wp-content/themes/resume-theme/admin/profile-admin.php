<?php
if (!defined('ABSPATH')) exit;

// 프로필 관리 페이지 함수
function resume_profile_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_profile';
    
    // 테이블이 존재하는지 확인
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        // 테이블이 없으면 생성
        resume_force_recreate_tables();
        resume_insert_sample_data();
        echo '<div class="updated"><p>프로필 테이블이 생성되었습니다.</p></div>';
    } else {
        // birth_date 컬럼이 없으면 추가
        resume_add_birth_date_column();
        // job_title 컬럼이 없으면 추가
        resume_add_job_title_column();
    }
    
    // 프로필 데이터 저장
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
        $existing = $wpdb->get_row("SELECT id FROM $table_name LIMIT 1");
        
        $data_to_save = array(
            'name' => sanitize_text_field($_POST['name']),
            'job_title' => sanitize_text_field($_POST['job_title']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'address' => sanitize_textarea_field($_POST['address']),
            'github' => esc_url_raw($_POST['github']),
            'velog' => esc_url_raw($_POST['velog']),
            'birth_date' => sanitize_text_field($_POST['birth_date']),
            'description' => wp_kses_post($_POST['description'])
        );
        
        // 프로필 사진 업로드 처리
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['size'] > 0) {
            $upload_result = resume_handle_file_upload($_FILES['profile_photo'], array('jpg', 'jpeg', 'png', 'gif'));
            if (isset($upload_result['url'])) {
                $data_to_save['photo'] = $upload_result['url'];
            } else {
                echo '<div class="error"><p>이미지 업로드 오류: ' . esc_html($upload_result['error']) . '</p></div>';
            }
        }
        
        if ($existing) {
            $wpdb->update($table_name, $data_to_save, array('id' => $existing->id));
            echo '<div class="updated"><p>프로필이 업데이트되었습니다.</p></div>';
        } else {
            $wpdb->insert($table_name, $data_to_save);
            echo '<div class="updated"><p>프로필이 저장되었습니다.</p></div>';
        }
    }
    
    // 저장된 데이터 가져오기
    $profile = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
    
    ?>
    <div class="wrap">
        <h1>프로필 관리</h1>
        
        <form method="post" class="resume-form" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th><label for="profile_photo">프로필 사진</label></th>
                    <td>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                        <?php if ($profile && $profile->photo): ?>
                            <div class="current-photo" style="margin-top: 10px;">
                                <p>현재 사진:</p>
                                <img src="<?php echo esc_url($profile->photo); ?>" style="max-width: 150px; border-radius: 10px; border: 1px solid #ddd;">
                            </div>
                        <?php endif; ?>
                        <p class="description">이미지 파일만 업로드 가능합니다. (JPG, PNG, GIF)</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="name">이름</label></th>
                    <td><input type="text" id="name" name="name" value="<?php echo esc_attr($profile->name ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="job_title">직무</label></th>
                    <td>
                        <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr($profile->job_title ?? ''); ?>" class="regular-text" placeholder="백엔드 개발자">
                        <p class="description">예: 백엔드 개발자, 프론트엔드 개발자, 풀스택 개발자</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="email">이메일</label></th>
                    <td><input type="email" id="email" name="email" value="<?php echo esc_attr($profile->email ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="phone">전화번호</label></th>
                    <td><input type="tel" id="phone" name="phone" value="<?php echo esc_attr($profile->phone ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="birth_date">생년월일</label></th>
                    <td><input type="text" id="birth_date" name="birth_date" value="<?php echo esc_attr($profile->birth_date ?? ''); ?>" class="regular-text" placeholder="2002.05.21"></td>
                </tr>
                <tr>
                    <th><label for="address">주소</label></th>
                    <td><textarea id="address" name="address" rows="3" class="large-text"><?php echo esc_textarea($profile->address ?? ''); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="github">GitHub</label></th>
                    <td><input type="url" id="github" name="github" value="<?php echo esc_url($profile->github ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="velog">블로그 (Velog)</label></th>
                    <td><input type="url" id="velog" name="velog" value="<?php echo esc_url($profile->velog ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="description">소개 / About Me</label></th>
                    <td>
                        <textarea id="description" name="description" rows="10" class="large-text"><?php echo esc_textarea($profile->description ?? ''); ?></textarea>
                        <p class="description">소개 내용을 입력하세요. HTML 태그를 사용할 수 있습니다. (&lt;ul&gt;, &lt;li&gt;, &lt;p&gt;, &lt;br&gt; 등)</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_profile" class="button button-primary" value="저장">
            </p>
        </form>
    </div>
    <?php
}

// 프로필 데이터 조회 (프론트엔드용)
function get_profile_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_profile';
    $result = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
    return $result ? $result : new stdClass();
} 