<?php
/**
 * 데이터베이스 테이블 강제 재생성 및 수정 스크립트
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 프로필 테이블에 job_title 컬럼 추가 함수
function resume_add_job_title_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_profile';
    
    // 테이블이 존재하는지 먼저 확인
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        return; // 테이블이 없으면 아무것도 하지 않음
    }
    
    // job_title 컬럼이 이미 존재하는지 확인
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'job_title'");
    
    if (empty($column_exists)) {
        // job_title 컬럼 추가
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `job_title` varchar(100) DEFAULT '백엔드 개발자' AFTER `name`");
    }
}

// 프로필 테이블에 birth_date 컬럼 추가 함수
function resume_add_birth_date_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_profile';
    
    // 테이블이 존재하는지 먼저 확인
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        return; // 테이블이 없으면 아무것도 하지 않음
    }
    
    // 컬럼이 이미 존재하는지 확인
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'birth_date'");
    
    if (empty($column_exists)) {
        // birth_date 컬럼 추가
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `birth_date` varchar(20) DEFAULT '2002.05.21' AFTER `address`");
    }
}

// 테이블 강제 재생성 함수
function resume_force_recreate_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // 기존 테이블 삭제 (주의: 데이터가 모두 삭제됩니다)
    $tables_to_drop = array(
        $wpdb->prefix . 'resume_profile',
        $wpdb->prefix . 'resume_skills',
        $wpdb->prefix . 'resume_projects',
        $wpdb->prefix . 'resume_education',
        $wpdb->prefix . 'resume_activities',
        $wpdb->prefix . 'resume_certifications',
        $wpdb->prefix . 'resume_training'
    );
    
    foreach ($tables_to_drop as $table) {
        $wpdb->query("DROP TABLE IF EXISTS `$table`");
    }

    // 프로필 테이블
    $profile_table = $wpdb->prefix . 'resume_profile';
    $sql_profile = "CREATE TABLE $profile_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL DEFAULT '나근민',
        job_title varchar(100) DEFAULT '백엔드 개발자',
        photo varchar(255),
        email varchar(100) DEFAULT 'rmsals8@naver.com',
        phone varchar(50) DEFAULT '010-4428-5895',
        address text,
        birth_date varchar(20) DEFAULT '2002.05.21',
        github varchar(255) DEFAULT 'https://github.com/rmsals8',
        velog varchar(255) DEFAULT 'https://velog.io/@hyu5895/posts',
        website varchar(255),
        description text,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // 스킬 테이블
    $skills_table = $wpdb->prefix . 'resume_skills';
    $sql_skills = "CREATE TABLE $skills_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        category varchar(50) NOT NULL,
        name varchar(100) NOT NULL,
        experience_type enum('experienced', 'theoretical') NOT NULL DEFAULT 'experienced',
        level tinyint(1) DEFAULT 3,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY category_idx (category),
        KEY experience_type_idx (experience_type)
    ) $charset_collate;";

    // 프로젝트 테이블
    $projects_table = $wpdb->prefix . 'resume_projects';
    $sql_projects = "CREATE TABLE $projects_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        organization varchar(255),
        description text,
        role varchar(100),
        team_size varchar(50),
        responsibilities text,
        technologies text,
        achievements text,
        start_date date,
        end_date date,
        github_url varchar(255),
        demo_url varchar(255),
        status enum('completed', 'ongoing', 'planned') DEFAULT 'completed',
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY start_date_idx (start_date),
        KEY status_idx (status)
    ) $charset_collate;";

    // 학력 테이블
    $education_table = $wpdb->prefix . 'resume_education';
    $sql_education = "CREATE TABLE $education_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        school varchar(255) NOT NULL,
        degree varchar(100),
        field varchar(100),
        grade varchar(20),
        total_credits varchar(20),
        start_date date,
        end_date date,
        description text,
        status enum('graduated', 'current', 'leave') DEFAULT 'graduated',
        certificate_file varchar(255),
        transcript_file varchar(255),
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY start_date_idx (start_date)
    ) $charset_collate;";

    // 활동 및 경험 테이블
    $activities_table = $wpdb->prefix . 'resume_activities';
    $sql_activities = "CREATE TABLE $activities_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        organization varchar(255),
        role varchar(100),
        start_date date,
        end_date date,
        description text,
        achievements text,
        certificate_file varchar(255),
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY start_date_idx (start_date)
    ) $charset_collate;";

    // 자격증 테이블
    $certifications_table = $wpdb->prefix . 'resume_certifications';
    $sql_certifications = "CREATE TABLE $certifications_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        organization varchar(255),
        certificate_number varchar(100),
        issue_date date,
        expiry_date date,
        score varchar(50),
        level varchar(50),
        description text,
        certificate_file varchar(255),
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY issue_date_idx (issue_date)
    ) $charset_collate;";

    // 교육 내용 테이블
    $training_table = $wpdb->prefix . 'resume_training';
    $sql_training = "CREATE TABLE $training_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        organization varchar(255),
        instructor varchar(100),
        start_date date,
        end_date date,
        duration varchar(50),
        description text,
        skills_learned text,
        certificate_file varchar(255),
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY start_date_idx (start_date)
    ) $charset_collate;";

    // 테이블 생성 실행
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $tables_created = array();
    $tables_failed = array();
    
    $sqls = array(
        'profile' => $sql_profile,
        'skills' => $sql_skills,
        'projects' => $sql_projects,
        'education' => $sql_education,
        'activities' => $sql_activities,
        'certifications' => $sql_certifications,
        'training' => $sql_training
    );
    
    foreach ($sqls as $table_name => $sql) {
        $result = $wpdb->query($sql);
        if ($result !== false) {
            $tables_created[] = $table_name;
        } else {
            $tables_failed[] = $table_name . ' (Error: ' . $wpdb->last_error . ')';
        }
    }
    
    return array('created' => $tables_created, 'failed' => $tables_failed);
}

// 학력 테이블에 총취득학점 컬럼 추가 함수
function resume_add_grade_columns() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_education';
    
    // 테이블이 존재하는지 먼저 확인
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        return; // 테이블이 없으면 아무것도 하지 않음
    }
    
    // total_credits 컬럼이 이미 존재하는지 확인
    $total_credits_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'total_credits'");
    if (empty($total_credits_exists)) {
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `total_credits` varchar(20) DEFAULT NULL AFTER `grade`");
    }
}

// 학력 테이블에 transcript_file 컬럼 추가 함수
function resume_add_transcript_file_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_education';
    
    // 테이블이 존재하는지 먼저 확인
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if (!$table_exists) {
        return; // 테이블이 없으면 아무것도 하지 않음
    }
    
    // transcript_file 컬럼이 이미 존재하는지 확인
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'transcript_file'");
    
    if (empty($column_exists)) {
        // transcript_file 컬럼 추가
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `transcript_file` varchar(255) DEFAULT NULL AFTER `certificate_file`");
    }
}

// 기본 데이터 삽입 함수 (강화 버전)
function resume_insert_sample_data() {
    global $wpdb;
    
    // 기본 프로필 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_profile");
    $wpdb->insert(
        $wpdb->prefix . 'resume_profile',
        array(
            'name' => '나근민',
            'job_title' => '백엔드 개발자',
            'email' => 'rmsals8@naver.com',
            'phone' => '010-4428-5895',
            'address' => '광주광역시 광산구 송정동 441-17',
            'birth_date' => '2002.05.21',
            'github' => 'https://github.com/rmsals8',
            'velog' => 'https://velog.io/@hyu5895/posts',
            'description' => '<ul><li>AI 기술과 풀스택 개발에 관심을 가지고 있는 예비 개발자로, 실제 운영 중인 상용 서비스 개발 경험 보유</li><li>현장실습을 통한 실무 경험으로 SR 처리, 시스템 최적화 등 현업 문제 해결 능력을 갖춘 학생</li><li>OpenAI API 부터 모바일 앱까지 다양한 기술 스택을 활용하여 사용자 중심의 혁신적인 서비스를 구현해본 경험</li><li>사용자에게 실질적 가치를 제공하는 AI 기반 서비스를 개발하며, 기술로 사회 문제를 해결하는 개발자가 되고자 함</li></ul>'
        )
    );
    
    // 기본 스킬 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_skills");
    $default_skills = array(
        // 경험 있는 스킬
        array('Programming Languages', 'Java', 'experienced'),
        array('Programming Languages', 'Python', 'experienced'),
        array('Programming Languages', 'JavaScript(ES6)', 'experienced'),
        array('Programming Languages', 'Dart', 'experienced'),
        array('Programming Languages', 'HTML5', 'experienced'),
        array('Programming Languages', 'CSS3', 'experienced'),
        array('Framework/ Library', 'Spring Boot', 'experienced'),
        array('Framework/ Library', 'Spring Security', 'experienced'),
        array('Framework/ Library', 'FastAPI', 'experienced'),
        array('Framework/ Library', 'React', 'experienced'),
        array('Framework/ Library', 'Vue.js', 'experienced'),
        array('Framework/ Library', 'Flutter', 'experienced'),
        array('Server', 'MariaDB', 'experienced'),
        array('Server', 'Redis', 'experienced'),
        array('Server', 'AWS S3', 'experienced'),
        array('Tooling/ DevOps', 'Git', 'experienced'),
        array('Tooling/ DevOps', 'GitLab', 'experienced'),
        array('Tooling/ DevOps', 'Maven', 'experienced'),
        array('Tooling/ DevOps', 'npm', 'experienced'),
        array('Tooling/ DevOps', 'Docker', 'experienced'),
        array('Environment', 'AWS', 'experienced'),
        array('Environment', 'Linux', 'experienced'),
        array('Environment', 'Windows', 'experienced'),
        array('ETC', 'OpenAI API', 'experienced'),
        array('ETC', 'LangChain', 'experienced'),
        array('ETC', 'Google Maps API', 'experienced'),
        array('ETC', 'Kakao API', 'experienced'),
        
        // 이론적 지식만 있는 스킬
        array('Programming Languages', 'TypeScript', 'theoretical'),
        array('Programming Languages', 'C++', 'theoretical'),
        array('Framework/ Library', 'React Native', 'theoretical'),
        array('Framework/ Library', 'Next.js', 'theoretical'),
        array('Framework/ Library', 'Spring Data JPA', 'theoretical'),
        array('Server', 'MongoDB', 'theoretical'),
        array('Tooling/ DevOps', 'Kubernetes', 'theoretical'),
        array('Environment', 'Firebase', 'theoretical')
    );
    
    foreach ($default_skills as $skill) {
        $wpdb->insert(
            $wpdb->prefix . 'resume_skills',
            array(
                'category' => $skill[0],
                'name' => $skill[1],
                'experience_type' => $skill[2]
            )
        );
    }
    
    // 기본 자격증 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_certifications");
    $default_certifications = array(
        array('TOEIC 860 점', '', '080149-0415009601', '2024-01-01', '', '860점', '어학'),
        array('OPIc IH (Intermediate High)', '', '2G732412935I', '2024-02-01', '', 'IH', '어학'),
        array('SQLD (SQL 개발자)', '한국데이터산업진흥원', 'SQLD-055019783', '2024-03-01', '', '합격', '국가공인'),
        array('ADsP (데이터분석 준전문가)', '한국데이터산업진흥원', 'ADsP-040017617', '2024-04-01', '', '합격', '국가공인'),
        array('정보처리기사', '한국산업인력공단', '', '2024-05-01', '', '필기 합격', '국가기술자격'),
        array('빅데이터분석기사', '한국산업인력공단', '', '2024-06-01', '', '필기 합격', '국가기술자격')
    );
    
    foreach ($default_certifications as $cert) {
        $wpdb->insert(
            $wpdb->prefix . 'resume_certifications',
            array(
                'name' => $cert[0],
                'organization' => $cert[1],
                'certificate_number' => $cert[2],
                'issue_date' => $cert[3],
                'expiry_date' => $cert[4],
                'score' => $cert[5],
                'level' => $cert[6]
            )
        );
    }
    
    // 기본 교육 내용 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_training");
    $wpdb->insert(
        $wpdb->prefix . 'resume_training',
        array(
            'title' => 'Future ICT Global Challenge Program (AI Training)',
            'organization' => 'University of Waterloo',
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-31',
            'duration' => '2개월',
            'description' => 'AI 기술과 ICT 분야의 글로벌 도전 과제를 해결하는 교육 프로그램',
            'skills_learned' => 'AI, Machine Learning, Data Analysis, Global Communication'
        )
    );
    
    // 기본 학력 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_education");
    $wpdb->insert(
        $wpdb->prefix . 'resume_education',
        array(
            'school' => '울산대학교',
            'degree' => '학사',
            'field' => 'IT 융합학부 AI 융합전공',
            'start_date' => '2021-03-01',
            'status' => 'current',
            'description' => '4 학년 1 학기 재학중'
        )
    );
    
    // 기본 활동 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_activities");
    $wpdb->insert(
        $wpdb->prefix . 'resume_activities',
        array(
            'title' => '㈜에스이에스케이 시스템 운영 인턴',
            'organization' => '현대모비스 HAIMS 운영팀',
            'role' => '시스템 운영 인턴',
            'start_date' => '2025-03-01',
            'description' => '현대모비스 HAIMS 시스템 운영 및 관리 업무 담당'
        )
    );
    
    // 기본 프로젝트 데이터 삽입
    $wpdb->query("DELETE FROM {$wpdb->prefix}resume_projects");
    $default_projects = array(
        array(
            'title' => 'Schedule Maker - 음성 기반 스마트 일정 관리 시스템',
            'team_size' => '개인 프로젝트',
            'description' => '졸업 캡스톤 프로젝트로 AI 와 모바일 기술을 융합한 혁신적 일정 관리 서비스 개발',
            'responsibilities' => '<ul><li>AI 음성 처리 서버 개발: LangChain + OpenAI GPT-4 를 활용한 자연어 음성을 구조화된 일정으로 변환하는 FastAPI 서버 구축</li><li>3 중 위치 검색 시스템: Kakao, Google, Foursquare API 를 통합하여 정확한 위치 정보 제공 시스템 설계</li><li>Flutter 모바일 앱 개발: 크로스플랫폼 앱으로 음성 인식, 실시간 네비게이션, 개인화 추천 기능 구현</li><li>통합 백엔드 서버: Spring Boot 기반 모놀리틱 구조로 인증, 일정 관리, 실시간 위치 추적 서비스 통합 개발</li></ul>',
            'technologies' => 'FastAPI, Python, Flutter, Dart, Spring Boot, Java, MariaDB, Redis, WebSocket, OpenAI API, Google Maps API',
            'achievements' => '<ul><li>복잡한 시스템을 3 개의 독립적인 서비스로 분리하여 개발하며 대규모 프로젝트 관리 능력 향상</li><li>AI 기술과 실시간 처리를 결합한 실용적인 서비스 구현을 통해 최신 기술 트렌드 적용 경험</li><li>아키텍처 선택에 대한 기술적 판단력과 프로젝트 상황에 맞는 유연한 설계 변경 능력 습득</li></ul>',
            'start_date' => '2024-09-01',
            'end_date' => '2025-05-31',
            'github_url' => 'https://github.com/rmsals8/capston-fastapi',
            'status' => 'ongoing'
        ),
        array(
            'title' => 'LingEdge - AI 기반 언어 학습 플랫폼 (실제 운영 중인 상용 서비스)',
            'team_size' => '개인 프로젝트',
            'description' => 'AI 기술을 활용한 혁신적 언어 학습 서비스 개발 및 실제 상용화',
            'responsibilities' => '<ul><li>AI 대화 생성 시스템: OpenAI GPT-3.5 API 를 활용하여 7 개 언어 지원 맞춤형 대화 생성</li><li>자동 문제 생성: PDF 기반 영작 문제 자동 생성 및 실시간 채점 시스템 구현</li><li>사용자 인증 시스템: JWT 기반 보안 인증 및 Google OAuth 소셜 로그인 구현</li><li>결제 시스템 연동: PayPal API 연동으로 프리미엄 구독 서비스 구현</li><li>클라우드 인프라: AWS S3 연동 파일 저장 및 자동 관리 시스템 구축</li></ul>',
            'technologies' => 'React, Spring Boot, MariaDB, OpenAI API, AWS S3',
            'achievements' => '<ul><li>단순한 프로젝트가 아닌 실제 사용자가 이용하는 상용 서비스를 구축하며 서비스 기획부터 운영까지 전 과정을 경험</li><li>AI 기술을 실무에 적용하는 방법과 사용자 경험을 고려한 서비스 설계의 중요성을 학습</li></ul>',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'github_url' => 'https://github.com/rmsals8/web-lingedge',
            'demo_url' => 'https://www.lingedge.com/',
            'status' => 'completed'
        )
    );
    
    foreach ($default_projects as $project) {
        $wpdb->insert($wpdb->prefix . 'resume_projects', $project);
    }
}

// 테이블 상태 확인 함수
function resume_check_table_status() {
    global $wpdb;
    
    $tables = array(
        'resume_profile',
        'resume_skills', 
        'resume_projects',
        'resume_education',
        'resume_activities',
        'resume_certifications',
        'resume_training'
    );
    
    $status = array();
    foreach ($tables as $table) {
        $full_table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
        $count = 0;
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
        }
        $status[$table] = array('exists' => $exists, 'count' => $count);
    }
    
    return $status;
}

// 관리자 페이지에서 호출할 수 있는 액션 추가
add_action('wp_ajax_resume_recreate_tables', function() {
    if (!current_user_can('manage_options')) {
        wp_die('권한이 없습니다.');
    }
    
    $result = resume_force_recreate_tables();
    resume_insert_sample_data();
    
    wp_send_json_success(array(
        'message' => '테이블이 성공적으로 재생성되었습니다.',
        'created' => $result['created'],
        'failed' => $result['failed']
    ));
});

// transcript_file 컬럼 추가 액션
add_action('wp_ajax_resume_add_transcript_column', function() {
    if (!current_user_can('manage_options')) {
        wp_die('권한이 없습니다.');
    }
    
    resume_add_transcript_file_column();
    
    wp_send_json_success(array(
        'message' => '성적증명서 컬럼이 성공적으로 추가되었습니다.'
    ));
});

// 초기화 시 자동으로 컬럼 추가
add_action('init', function() {
    if (is_admin()) {
        resume_add_job_title_column();
        resume_add_grade_columns();
        resume_add_transcript_file_column();
    }
});

?>