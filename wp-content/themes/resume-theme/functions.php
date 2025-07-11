<?php
/**
 * Resume Theme Functions - 나근민 스타일 (데이터베이스 수정 버전)
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 데이터베이스 수정 파일 포함
require_once get_template_directory() . '/admin/database-fix.php';

// 관리자 파일 포함
require_once get_template_directory() . '/admin/profile-admin.php';
require_once get_template_directory() . '/admin/skills-admin.php';
require_once get_template_directory() . '/admin/projects-admin.php';
require_once get_template_directory() . '/admin/education-admin.php';
require_once get_template_directory() . '/admin/activities-admin.php';
require_once get_template_directory() . '/admin/certifications-admin.php';
require_once get_template_directory() . '/admin/training-admin.php';
require_once get_template_directory() . '/admin/awards-admin.php';
require_once get_template_directory() . '/admin/download-admin.php';

// 테마 활성화 시 테이블 강제 재생성
function resume_theme_create_tables() {
    // 기존 함수 대신 강제 재생성 함수 사용
    $result = resume_force_recreate_tables();
    resume_insert_sample_data();
    
    // 결과를 옵션에 저장해서 관리자에게 알림
    update_option('resume_table_creation_result', $result);
}
add_action('after_switch_theme', 'resume_theme_create_tables');

// 관리자 알림 표시
function resume_admin_notices() {
    $result = get_option('resume_table_creation_result');
    if ($result) {
        $created_count = count($result['created']);
        $failed_count = count($result['failed']);
        
        if ($created_count > 0) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Resume Theme:</strong> ' . $created_count . '개의 테이블이 성공적으로 생성되었습니다.</p>';
            if (!empty($result['created'])) {
                echo '<p>생성된 테이블: ' . implode(', ', $result['created']) . '</p>';
            }
            echo '</div>';
        }
        
        if ($failed_count > 0) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>Resume Theme:</strong> ' . $failed_count . '개의 테이블 생성에 실패했습니다.</p>';
            if (!empty($result['failed'])) {
                echo '<p>실패한 테이블: ' . implode(', ', $result['failed']) . '</p>';
            }
            echo '</div>';
        }
        
        // 한 번 표시한 후 삭제
        delete_option('resume_table_creation_result');
    }
    
    // 테이블 상태 확인 및 문제가 있으면 자동 생성
    $table_status = resume_check_table_status();
    $missing_tables = array();
    $empty_tables = array();
    
    foreach ($table_status as $table => $status) {
        if (!$status['exists']) {
            $missing_tables[] = $table;
        } elseif ($status['count'] == 0 && in_array($table, array('resume_skills', 'resume_certifications'))) {
            $empty_tables[] = $table;
        }
    }
    
    // 누락된 테이블이나 빈 테이블이 있으면 자동으로 재생성
    if (!empty($missing_tables) || !empty($empty_tables)) {
        resume_force_recreate_tables();
        resume_insert_sample_data();
        
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Resume Theme:</strong> 누락된 테이블을 자동으로 생성하고 기본 데이터를 추가했습니다.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'resume_admin_notices');

// 관리자 페이지 공통 헤더
function resume_admin_header($title) {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html($title); ?></h1>
        <?php
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
            $class = $type === 'error' ? 'notice-error' : 'notice-success';
            ?>
            <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
            <?php
        }
        ?>
    </div>
    
    <script>
    function recreateResumeTablees() {
        if (confirm('테이블을 재생성하면 기존 데이터가 모두 삭제됩니다. 계속하시겠습니까?')) {
            jQuery.post(ajaxurl, {
                action: 'resume_recreate_tables',
                _ajax_nonce: '<?php echo wp_create_nonce('resume_recreate_tables'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('테이블이 성공적으로 재생성되었습니다. 페이지를 새로고침합니다.');
                    location.reload();
                } else {
                    alert('테이블 재생성에 실패했습니다: ' + response.data);
                }
            });
        }
    }
    </script>
    <?php
}

// 관리자 메뉴 등록
function resume_register_admin_menu() {
    add_menu_page(
        '이력서 관리자',
        '이력서 관리자',
        'manage_options',
        'resume-education',
        'resume_education_page',
        'dashicons-id-alt',
        30
    );
    

    
    add_submenu_page(
        'resume-education',
        '프로필 관리',
        '프로필',
        'manage_options',
        'resume-profile',
        'resume_profile_page'
    );
    
    add_submenu_page(
        'resume-education',
        '기술 스택 관리',
        '기술 스택',
        'manage_options',
        'resume-skills',
        'resume_skills_page'
    );
    
    add_submenu_page(
        'resume-education',
        ' 프로젝트 관리',
        '프로젝트',
        'manage_options',
        'resume-projects',
        'resume_projects_page'
    );
    
    add_submenu_page(
        'resume-education',
        '서류 일괄 다운로드',
        '서류 다운로드',
        'manage_options',
        'resume-download',
        'resume_download_page'
    );
    
    add_submenu_page(
        'resume-education',
        '학력 관리',
        '학력',
        'manage_options',
        'resume-education',
        'resume_education_page'
    );
    
    add_submenu_page(
        'resume-education',
        '활동 및 경험',
        '활동/경험',
        'manage_options',
        'resume-activities',
        'resume_activities_page'
    );
    
    add_submenu_page(
        'resume-education',
        '자격증 관리',
        '자격증',
        'manage_options',
        'resume-certifications',
        'resume_certifications_page'
    );
    
    add_submenu_page(
        'resume-education',
        '교육 내용',
        '교육 내용',
        'manage_options',
        'resume-training',
        'resume_training_page'
    );

    add_submenu_page(
        'resume-education',
        '수상 이력',
        '수상 이력',
        'manage_options',
        'resume-awards',
        'resume_awards_page'
    );
}
add_action('admin_menu', 'resume_register_admin_menu');

// 데이터 검증 및 정리 헬퍼 함수
function resume_sanitize_data($data, $type = 'text') {
    switch ($type) {
        case 'email':
            return sanitize_email($data);
        case 'url':
            return esc_url_raw($data);
        case 'textarea':
            return wp_kses_post($data);
        case 'date':
            return sanitize_text_field($data);
        case 'html':
            return wp_kses($data, array(
                'ul' => array(),
                'li' => array(),
                'p' => array(),
                'br' => array(),
                'strong' => array(),
                'em' => array(),
                'a' => array('href' => array(), 'target' => array())
            ));
        default:
            return sanitize_text_field($data);
    }
}

// 파일 업로드 처리 함수
function resume_handle_file_upload($file, $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx')) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    // 파일 확장자 검사
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return array('error' => '허용되지 않은 파일 형식입니다. 허용 형식: ' . implode(', ', $allowed_types));
    }
    
    // 파일 크기 검사 (5MB 제한)
    if ($file['size'] > 5 * 1024 * 1024) {
        return array('error' => '파일 크기는 5MB를 초과할 수 없습니다.');
    }
    
    $upload_overrides = array(
        'test_form' => false,
        'mimes' => array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        )
    );
    
    $movefile = wp_handle_upload($file, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        return array('url' => $movefile['url'], 'path' => $movefile['file']);
    } else {
        return array('error' => $movefile['error']);
    }
}

// 관리자 스타일 추가
function resume_theme_admin_styles($hook) {
    // 이력서 관리자 페이지에서만 스타일 로드
    if (strpos($hook, 'resume-') === false && $hook !== 'toplevel_page_resume-education') {
        return;
    }
    
    wp_enqueue_style(
        'resume-admin-style', 
        get_template_directory_uri() . '/admin/css/admin-style.css', 
        array(), 
        '2.0.0'
    );
    
    wp_enqueue_style(
        'noto-sans-kr', 
        'https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap'
    );
}
add_action('admin_enqueue_scripts', 'resume_theme_admin_styles');

// 테마 설정
function resume_theme_setup() {
    // 테마 지원 기능 추가
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // 커스텀 로고 지원
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 100,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // 메뉴 등록
    register_nav_menus(array(
        'primary-menu' => '기본 메뉴',
    ));
}
add_action('after_setup_theme', 'resume_theme_setup');

// 스크립트 및 스타일 로드
function resume_theme_enqueue_scripts() {
    // 메인 스타일시트 (타임스키프로 캠시 방지)
    wp_enqueue_style('resume-theme-style', get_stylesheet_uri(), array(), time());
    
    // Google Fonts
    wp_enqueue_style(
        'noto-sans-kr', 
        'https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );
    
    // Font Awesome (아이콘용)
    wp_enqueue_style(
        'font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        array(),
        '6.0.0'
    );
    
    // 관리자 페이지에서 추가 스크립트
    if (is_admin()) {
        wp_enqueue_script(
            'resume-admin', 
            get_template_directory_uri() . '/js/admin.js', 
            array('jquery'), 
            '2.0.0', 
            true
        );
        
        wp_localize_script('resume-admin', 'resumeAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resume_nonce')
        ));
        
        // 미디어 업로더
        wp_enqueue_media();
    }
    
    // 프론트엔드에서 추가 스크립트
    if (!is_admin()) {
        wp_enqueue_script(
            'resume-theme-script', 
            get_template_directory_uri() . '/js/resume.js', 
            array('jquery'), 
            '2.0.0', 
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'resume_theme_enqueue_scripts');
add_action('admin_enqueue_scripts', 'resume_theme_enqueue_scripts');

// 미리보기 생성 함수
function resume_generate_file_preview($file_url) {
    if (empty($file_url)) {
        return '';
    }
    
    $file_extension = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
    
    if (in_array($file_extension, array('jpg', 'jpeg', 'png', 'gif'))) {
        // 이미지 파일
        return '<img src="' . esc_url($file_url) . '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;" alt="미리보기">';
    } elseif ($file_extension === 'pdf') {
        // PDF 파일
        return '<iframe src="' . esc_url($file_url) . '" style="width: 300px; height: 200px; border: 1px solid #ddd; border-radius: 4px;"></iframe>';
    } else {
        // 기타 파일
        return '<div style="padding: 20px; border: 1px solid #ddd; border-radius: 4px; text-align: center; background: #f9f9f9;">
                    <i class="fas fa-file" style="font-size: 48px; color: #666; margin-bottom: 10px;"></i><br>
                    <strong>' . basename($file_url) . '</strong><br>
                    <small>파일을 다운로드하여 확인하세요</small>
                </div>';
    }
}

// 커스텀 body 클래스 추가
function resume_body_classes($classes) {
    if (is_admin()) {
        $classes[] = 'resume-admin';
    } else {
        $classes[] = 'resume-frontend';
    }
    return $classes;
}
add_filter('body_class', 'resume_body_classes');

// AJAX 액션 등록 수정
add_action('wp_ajax_resume_recreate_tables', function() {
    // nonce 검증
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'resume_recreate_tables')) {
        wp_send_json_error('보안 검증에 실패했습니다.');
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('권한이 없습니다.');
    }
    
    $result = resume_force_recreate_tables();
    resume_insert_sample_data();
    
    wp_send_json_success(array(
        'message' => '테이블이 성공적으로 재생성되었습니다.',
        'created' => $result['created'],
        'failed' => $result['failed']
    ));
});

// 서류 일괄 다운로드 기능
add_action('wp_ajax_resume_download_documents', function() {
    // nonce 검증
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'resume_download_documents')) {
        wp_send_json_error('보안 검증에 실패했습니다.');
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('권한이 없습니다.');
    }
    
    // ZipArchive 클래스 확인
    if (!class_exists('ZipArchive')) {
        wp_send_json_error('ZipArchive 클래스를 사용할 수 없습니다.');
    }
    
    $result = resume_create_documents_zip();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'download_url' => $result['download_url'],
            'file_count' => $result['file_count']
        ));
    } else {
        wp_send_json_error($result['message']);
    }
});

// 서류 ZIP 파일 생성 함수
function resume_create_documents_zip() {
    global $wpdb;
    
    // 디버깅용 로그 함수
    function debug_log($message) {
        error_log('[Resume Debug] ' . $message);
    }
    
    $zip = new ZipArchive();
    $upload_dir = wp_upload_dir();
    $zip_filename = 'resume_documents_' . date('Y-m-d_H-i-s') . '.zip';
    $zip_path = $upload_dir['path'] . '/' . $zip_filename;
    
    if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
        return array('success' => false, 'message' => 'ZIP 파일을 생성할 수 없습니다.');
    }
    
    $file_count = 0;
    $debug_info = array();
    
    // 1. 주요활동 및 사회경험
    $activities = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_activities");
    debug_log('Activities count: ' . count($activities));
    if (!empty($activities)) {
        foreach ($activities as $activity) {
            debug_log('Activity: ' . $activity->title . ', File: ' . $activity->certificate_file);
            if (!empty($activity->certificate_file)) {
                $file_content = resume_download_file($activity->certificate_file);
                if ($file_content) {
                    $filename = sanitize_file_name($activity->title) . '_경력증명서.' . pathinfo($activity->certificate_file, PATHINFO_EXTENSION);
                    $zip->addFromString('주요활동_및_사회경험/' . $filename, $file_content);
                    $file_count++;
                    $debug_info[] = '활동: ' . $filename;
                }
            }
        }
    }
    
    // 2. 학력
    $education = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_education");
    debug_log('Education count: ' . count($education));
    if (!empty($education)) {
        foreach ($education as $edu) {
            $school_name = sanitize_file_name($edu->school);
            debug_log('Education: ' . $edu->school . ', Cert: ' . $edu->certificate_file . ', Trans: ' . (isset($edu->transcript_file) ? $edu->transcript_file : 'N/A'));
            
            // 졸업/재학증명서
            if (!empty($edu->certificate_file)) {
                $file_content = resume_download_file($edu->certificate_file);
                if ($file_content) {
                    $filename = $school_name . '_졸업재학증명서.' . pathinfo($edu->certificate_file, PATHINFO_EXTENSION);
                    $zip->addFromString('학력/' . $filename, $file_content);
                    $file_count++;
                    $debug_info[] = '학력: ' . $filename;
                }
            }
            
            // 성적증명서
            if (isset($edu->transcript_file) && !empty($edu->transcript_file)) {
                $file_content = resume_download_file($edu->transcript_file);
                if ($file_content) {
                    $filename = $school_name . '_성적증명서.' . pathinfo($edu->transcript_file, PATHINFO_EXTENSION);
                    $zip->addFromString('학력/' . $filename, $file_content);
                    $file_count++;
                    $debug_info[] = '학력: ' . $filename;
                }
            }
        }
    }
    
    // 3. 자격증
    $certifications = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_certifications");
    debug_log('Certifications count: ' . count($certifications));
    if (!empty($certifications)) {
        foreach ($certifications as $cert) {
            debug_log('Cert: ' . $cert->name . ', File: ' . $cert->certificate_file);
            if (!empty($cert->certificate_file)) {
                $file_content = resume_download_file($cert->certificate_file);
                if ($file_content) {
                    $filename = sanitize_file_name($cert->name) . '_자격증.' . pathinfo($cert->certificate_file, PATHINFO_EXTENSION);
                    $zip->addFromString('자격증/' . $filename, $file_content);
                    $file_count++;
                    $debug_info[] = '자격증: ' . $filename;
                }
            }
        }
    }
    
    // 4. 교육 내용
    $trainings = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}resume_training");
    debug_log('Trainings count: ' . count($trainings));
    if (!empty($trainings)) {
        foreach ($trainings as $training) {
            debug_log('Training: ' . $training->title . ', File: ' . $training->certificate_file);
            if (!empty($training->certificate_file)) {
                $file_content = resume_download_file($training->certificate_file);
                if ($file_content) {
                    $filename = sanitize_file_name($training->title) . '_수료증.' . pathinfo($training->certificate_file, PATHINFO_EXTENSION);
                    $zip->addFromString('교육_내용/' . $filename, $file_content);
                    $file_count++;
                    $debug_info[] = '교육: ' . $filename;
                }
            }
        }
    }
    
    $zip->close();
    
    debug_log('Total files found: ' . $file_count);
    debug_log('Debug info: ' . implode(', ', $debug_info));
    
    if ($file_count === 0) {
        unlink($zip_path);
        
        // 디버그 정보 수집
        $debug_summary = array(
            'activities' => count($activities),
            'education' => count($education), 
            'certifications' => count($certifications),
            'trainings' => count($trainings)
        );
        
        $error_message = '다운로드할 서류가 없습니다. '
                      . '테이블 데이터: '
                      . '활동(' . $debug_summary['activities'] . '개), '
                      . '학력(' . $debug_summary['education'] . '개), '
                      . '자격증(' . $debug_summary['certifications'] . '개), '
                      . '교육(' . $debug_summary['trainings'] . '개)';
        
        return array('success' => false, 'message' => $error_message);
    }
    
    return array(
        'success' => true,
        'download_url' => $upload_dir['url'] . '/' . $zip_filename,
        'file_count' => $file_count,
        'debug_info' => $debug_info
    );
}

// 파일 다운로드 함수
function resume_download_file($url) {
    if (empty($url)) {
        error_log('[Resume Debug] Empty URL provided');
        return false;
    }
    
    error_log('[Resume Debug] Trying to download: ' . $url);
    
    // 로컬 파일인지 확인
    $upload_dir = wp_upload_dir();
    if (strpos($url, $upload_dir['baseurl']) !== false) {
        // 로컬 파일이면 직접 읽기
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
            error_log('[Resume Debug] Local file read success, size: ' . strlen($content));
            return $content;
        } else {
            error_log('[Resume Debug] Local file not found: ' . $file_path);
        }
    }
    
    // 원격 파일 다운로드
    $response = wp_remote_get($url, array(
        'timeout' => 30,
        'sslverify' => false
    ));
    
    if (is_wp_error($response)) {
        error_log('[Resume Debug] wp_remote_get error: ' . $response->get_error_message());
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $success = !empty($body);
    error_log('[Resume Debug] Remote download ' . ($success ? 'success' : 'failed') . ', size: ' . strlen($body));
    
    return $success ? $body : false;
}

// 워드프레스 버전 호환성 체크
function resume_check_wp_version() {
    global $wp_version;
    $required_wp_version = '5.0';
    
    if (version_compare($wp_version, $required_wp_version, '<')) {
        add_action('admin_notices', function() use ($required_wp_version) {
            echo '<div class="notice notice-error">';
            echo '<p><strong>Resume Theme:</strong> 이 테마는 WordPress ' . $required_wp_version . ' 이상에서 작동합니다. 현재 버전: ' . get_bloginfo('version') . '</p>';
            echo '</div>';
        });
    }
}
add_action('admin_init', 'resume_check_wp_version');

// 테마 비활성화 시 정리 (선택사항)
function resume_theme_deactivation() {
    // 임시 옵션 정리
    delete_option('resume_table_creation_result');
}
add_action('switch_theme', 'resume_theme_deactivation');

?>