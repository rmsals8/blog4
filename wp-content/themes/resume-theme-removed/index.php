<?php get_header(); ?>

<div class="container">
    <?php
    global $wpdb;
    
    // 안전한 데이터 가져오기 함수들
    function get_safe_profile_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_profile';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return null;
        }
        return $wpdb->get_row("SELECT * FROM $table LIMIT 1");
    }
    
    function get_safe_skills_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_skills';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        return $wpdb->get_results("SELECT * FROM $table ORDER BY category, name");
    }
    
    function get_safe_projects_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_projects';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        return $wpdb->get_results("SELECT * FROM $table ORDER BY start_date DESC");
    }
    
    function get_safe_education_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_education';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        return $wpdb->get_results("SELECT * FROM $table ORDER BY start_date DESC");
    }
    
    function get_safe_activities_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_activities';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        return $wpdb->get_results("SELECT * FROM $table ORDER BY start_date DESC");
    }
    
    function get_safe_certifications_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_certifications';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        // issue_date 컬럼이 존재하는지 확인
        $columns = $wpdb->get_col("DESCRIBE $table");
        if (in_array('issue_date', $columns)) {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY issue_date DESC");
        } else {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
        }
    }
    
    function get_safe_training_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_training';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        return $wpdb->get_results("SELECT * FROM $table ORDER BY start_date DESC");
    }
    
    // 데이터 가져오기
    $profile = get_safe_profile_data();
    $skills = get_safe_skills_data();
    $projects = get_safe_projects_data();
    $education = get_safe_education_data();
    $activities = get_safe_activities_data();
    $certifications = get_safe_certifications_data();
    $trainings = get_safe_training_data();
    // Awards
    function get_safe_awards_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'resume_awards';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return array();
        }
        return $wpdb->get_results("SELECT * FROM $table ORDER BY award_date DESC");
    }
    $awards = get_safe_awards_data();
    
    // 테이블이 없거나 데이터가 없을 때 관리자에게 알림
    $missing_tables = array();
    if (!$profile) $missing_tables[] = 'profile';
    if (empty($skills)) $missing_tables[] = 'skills';
    if (empty($certifications)) $missing_tables[] = 'certifications';
    
    if (!empty($missing_tables) && current_user_can('manage_options')):
    ?>
        <div class="admin-notice">
            <p><strong>관리자 알림:</strong> 일부 데이터가 없거나 테이블이 생성되지 않았습니다.</p>
            <p>WordPress 관리자 → <a href="<?php echo admin_url('admin.php?page=resume-manager'); ?>">이력서 관리자</a>에서 데이터를 추가하거나 테이블을 재생성하세요.</p>
        </div>
    <?php endif; ?>

    <!-- 프로필 섹션 -->
    <section class="profile-header">
        <?php if ($profile && $profile->photo): ?>
            <img src="<?php echo esc_url($profile->photo); ?>" alt="<?php echo esc_attr($profile->name); ?>" class="profile-photo">
        <?php endif; ?>
        
        <div class="profile-info">
            <div class="profile-name-section">
                <div class="profile-name-container">
                    <h1><?php echo $profile ? esc_html($profile->name) : '나근민'; ?></h1>
                    <div class="profile-subtitle">(<?php echo $profile && $profile->job_title ? esc_html($profile->job_title) : '백엔드 개발자'; ?>)</div>
                </div>
            </div>
            
            <div class="contact-info">
                <div class="contact-item">
                    <span class="contact-label">Birthday</span>
                    <span class="contact-value"><?php echo $profile && $profile->birth_date ? esc_html($profile->birth_date) : '2002.05.21'; ?></span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Email</span>
                    <span class="contact-value">
                        <a href="mailto:<?php echo $profile ? esc_attr($profile->email) : 'rmsals8@naver.com'; ?>"><?php echo $profile ? esc_html($profile->email) : 'rmsals8@naver.com'; ?></a>
                    </span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Mobile</span>
                    <span class="contact-value"><?php echo $profile ? esc_html($profile->phone) : '010-4428-5895'; ?></span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Address</span>
                    <span class="contact-value"><?php echo $profile ? esc_html($profile->address) : '광주광역시 광산구 송정동 441-17'; ?></span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Github</span>
                    <span class="contact-value">
                        <a href="<?php echo $profile ? esc_url($profile->github) : 'https://github.com/rmsals8'; ?>" target="_blank"><?php echo $profile ? esc_html($profile->github) : 'https://github.com/rmsals8'; ?></a>
                    </span>
                </div>
                <div class="contact-item">
                    <span class="contact-label">블로그</span>
                    <span class="contact-value">
                        <a href="<?php echo $profile ? esc_url($profile->velog) : 'https://velog.io/@hyu5895/posts'; ?>" target="_blank"><?php echo $profile ? esc_html($profile->velog) : 'https://velog.io/@hyu5895/posts'; ?></a>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- 소개 / About Me 섹션 -->
    <section class="resume-section">
        <h2>소개 / About Me</h2>
        <div class="about-content">
            <?php if ($profile && $profile->description): ?>
                <?php 
                // 텍스트를 줄바꿈으로 분할하고 각 줄을 div로 감싸기
                $lines = explode("\n", trim($profile->description));
                echo '<div class="auto-bullet-list">';
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        echo '<div class="auto-bullet-item">' . esc_html($line) . '</div>';
                    }
                }
                echo '</div>';
                ?>
            <?php else: ?>
                <div class="auto-bullet-list">
                    <div class="auto-bullet-item">AI 기술과 풀스택 개발에 관심을 가지고 있는 예비 개발자로, 실제 운영 중인 상용 서비스 개발 경험 보유</div>
                    <div class="auto-bullet-item">현장실습을 통한 실무 경험으로 SR 처리, 시스템 최적화 등 현업 문제 해결 능력을 갖춘 학생</div>
                    <div class="auto-bullet-item">OpenAI API 부터 모바일 앱까지 다양한 기술 스택을 활용하여 사용자 중심의 혁신적인 서비스를 구현해본 경험</div>
                    <div class="auto-bullet-item">사용자에게 실질적 가치를 제공하는 AI 기반 서비스를 개발하며, 기술로 사회 문제를 해결하는 개발자가 되고자 함</div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 기술 스택 / Skill Set 섹션 -->
    <section class="resume-section">
        <h2>기술 스택 / Skill Set</h2>
        
        <div class="skills-container">
            <div class="skills-category-title">기능 구현 등의 사용 경험이 있는 Skill Set</div>
            <table class="skills-table">
                <thead>
                    <tr>
                        <th>구분</th>
                        <th>Skill</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 경험 있는 스킬들을 카테고리별로 그룹화
                    $experienced_skills = array();
                    foreach ($skills as $skill) {
                        if ($skill->experience_type === 'experienced') {
                            if (!isset($experienced_skills[$skill->category])) {
                                $experienced_skills[$skill->category] = array();
                            }
                            $experienced_skills[$skill->category][] = $skill->name;
                        }
                    }
                    
                    if (!empty($experienced_skills)) {
                        foreach ($experienced_skills as $category => $skill_list): ?>
                            <tr>
                                <td><?php echo esc_html($category); ?></td>
                                <td><?php echo esc_html(implode(', ', $skill_list)); ?></td>
                            </tr>
                        <?php endforeach;
                    } else {
                        echo '<tr><td colspan="2">등록된 기술 스택이 없습니다.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="skills-container">
            <div class="skills-category-title">사용경험은 없으나, 이론적 지식이 있는 Skill Set</div>
            <table class="skills-table">
                <thead>
                    <tr>
                        <th>구분</th>
                        <th>Skill</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 이론적 지식만 있는 스킬들을 카테고리별로 그룹화
                    $theoretical_skills = array();
                    foreach ($skills as $skill) {
                        if ($skill->experience_type === 'theoretical') {
                            if (!isset($theoretical_skills[$skill->category])) {
                                $theoretical_skills[$skill->category] = array();
                            }
                            $theoretical_skills[$skill->category][] = $skill->name;
                        }
                    }
                    
                    if (!empty($theoretical_skills)) {
                        foreach ($theoretical_skills as $category => $skill_list): ?>
                            <tr>
                                <td><?php echo esc_html($category); ?></td>
                                <td><?php echo esc_html(implode(', ', $skill_list)); ?></td>
                            </tr>
                        <?php endforeach;
                    } else {
                        echo '<tr><td colspan="2">등록된 기술 스택이 없습니다.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 과제 프로젝트 경험 섹션 -->
    <section class="resume-section">
        <h2>과제 프로젝트 경험</h2>
        
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-item">
                    <div class="project-header">
                        <h3 class="project-title"><?php echo esc_html($project->title); ?></h3>
                    </div>
                    
                    <table class="project-meta-table">
                        <tbody>
                            <tr>
                                <td class="project-meta-label">작업 기간</td>
                                <td class="project-meta-value">
                                    <?php 
                                    if ($project->start_date) {
                                        echo esc_html(date('Y.m', strtotime($project->start_date)));
                                        if ($project->end_date) {
                                            echo ' ~ ' . esc_html(date('Y.m', strtotime($project->end_date)));
                                        } else {
                                            echo ' ~ 현재';
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-meta-label">인력 구성</td>
                                <td class="project-meta-value"><?php echo $project->organization ? esc_html($project->organization) : ($project->team_size ? esc_html($project->team_size) : '개인 프로젝트'); ?></td>
                            </tr>
                            <?php if ($project->role): ?>
                            <tr>
                                <td class="project-meta-label">역할</td>
                                <td class="project-meta-value"><?php echo esc_html($project->role); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="project-meta-label">프로젝트 목적</td>
                                <td class="project-meta-value"><?php echo esc_html($project->description); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if ($project->responsibilities): ?>
                        <div class="project-details">
                            <h4>주요업무 및 상세역할</h4>
                            <?php 
                            // 텍스트를 줄바꿈으로 분할하고 각 줄을 div로 감싸기
                            $resp_lines = explode("\n", trim($project->responsibilities));
                            echo '<div class="auto-bullet-list">';
                            foreach ($resp_lines as $line) {
                                $line = trim($line);
                                if (!empty($line)) {
                                    echo '<div class="auto-bullet-item">' . esc_html($line) . '</div>';
                                }
                            }
                            echo '</div>';
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($project->technologies): ?>
                        <div class="project-details">
                            <h4>사용언어 및 개발환경</h4>
                            <?php 
                            // 텍스트를 줄바꿈으로 분할하고 표로 변환
                            $tech_lines = explode("\n", trim($project->technologies));
                            if (!empty($tech_lines)) {
                                echo '<table class="project-tech-table">';
                                echo '<thead><tr><th>구분</th><th>구성 스택</th></tr></thead>';
                                echo '<tbody>';
                                foreach ($tech_lines as $line) {
                                    $line = trim($line);
                                    if (!empty($line)) {
                                        // 줄에서 콤마나 콜론으로 구분된 부분을 찾기
                                        if (strpos($line, ',') !== false) {
                                            $parts = explode(',', $line, 2);
                                            $category = trim($parts[0]);
                                            $stack = trim($parts[1]);
                                        } else {
                                            $category = '기타';
                                            $stack = $line;
                                        }
                                        echo '<tr>';
                                        echo '<td>' . esc_html($category) . '</td>';
                                        echo '<td>' . esc_html($stack) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                                echo '</tbody></table>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($project->achievements): ?>
                        <div class="project-details">
                            <h4>느낀 점</h4>
                            <?php 
                            // 텍스트를 줄바꿈으로 분할하고 각 줄을 div로 감싸기
                            $ach_lines = explode("\n", trim($project->achievements));
                            echo '<div class="auto-bullet-list">';
                            foreach ($ach_lines as $line) {
                                $line = trim($line);
                                if (!empty($line)) {
                                    echo '<div class="auto-bullet-item">' . esc_html($line) . '</div>';
                                }
                            }
                            echo '</div>';
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($project->github_url || $project->demo_url): ?>
                        <div class="project-links">
                            <strong>참고자료</strong><br>
                            <?php if ($project->github_url): ?>
                                GitHub:
                                <?php
                                $links = array_filter(array_map('trim', explode(',', $project->github_url)));
                                $first = true;
                                foreach ($links as $link) {
                                    if (!$first) echo ', ';
                                    $first = false;
                                    $esc = esc_url($link);
                                    echo '<a href="' . $esc . '" target="_blank">' . $esc . '</a>';
                                }
                                ?>
                                <br>
                            <?php endif; ?>
                            <?php if ($project->demo_url): ?>
                                <?php $demo_label = !empty($project->demo_url_label) ? $project->demo_url_label : '실제 서비스 운영 중'; ?>
                                <?php echo esc_html($demo_label); ?>: <a href="<?php echo esc_url($project->demo_url); ?>" target="_blank"><?php echo esc_url($project->demo_url); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data-message">
                <p>등록된 프로젝트가 없습니다.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- 주요활동 및 사회경험 섹션 -->
    <?php if (!empty($activities)): ?>
    <section class="resume-section">
        <h2>주요활동 및 사회경험</h2>
        
        <?php foreach ($activities as $activity): ?>
            <div class="activity-item">
                <h3 class="activity-title">
                    <?php 
                    if ($activity->start_date) {
                        echo esc_html(date('Y.m', strtotime($activity->start_date)));
                        if ($activity->end_date) {
                            echo ' ~ ' . esc_html(date('Y.m', strtotime($activity->end_date)));
                        } else {
                            echo ' ~ 현재';
                        }
                        echo ' ';
                    }
                    echo esc_html($activity->title);
                    if ($activity->organization) {
                        echo ' (' . esc_html($activity->organization) . ')';
                    }
                    ?>
                </h3>
                
                <?php if ($activity->description): ?>
                    <div class="activity-description"><?php echo wp_kses_post($activity->description); ?></div>
                <?php endif; ?>
                
                <?php if ($activity->certificate_file): ?>
                    <div class="activity-file">
                        <a href="<?php echo esc_url($activity->certificate_file); ?>" target="_blank" class="certification-preview-btn" data-filename="<?php echo esc_attr($activity->title); ?>">
                            <i class="fas fa-file-alt"></i> 경력증명서 보기
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- 학력 섹션 -->
    <?php if (!empty($education)): ?>
    <section class="resume-section">
        <h2>학력</h2>
        
        <?php foreach ($education as $edu): ?>
            <div class="education-item">
                <h3 class="education-school"><?php echo esc_html($edu->school); ?></h3>
                <?php if ($edu->degree && $edu->field): ?>
                    <div class="education-degree"><?php echo esc_html($edu->degree); ?> <?php echo esc_html($edu->field); ?></div>
                <?php endif; ?>
                <div class="education-period">
                    <?php 
                    if ($edu->start_date) {
                        echo esc_html(date('Y.m', strtotime($edu->start_date)));
                        if ($edu->end_date) {
                            echo ' ~ ' . esc_html(date('Y.m', strtotime($edu->end_date)));
                        } else {
                            echo ' ~ 현재';
                        }
                    }
                    ?>
                </div>
                
                <?php if ($edu->grade): ?>
                    <div class="education-grade-basic">
                        <span>학점/성적: <?php echo esc_html($edu->grade); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($edu->total_credits) && $edu->total_credits): ?>
                    <div class="education-grade-info">
                        <span>총취득학점: <?php echo esc_html($edu->total_credits); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($edu->description): ?>
                    <div class="education-description"><?php echo wp_kses_post($edu->description); ?></div>
                <?php endif; ?>
                
                <div class="education-file">
                    <?php if ($edu->certificate_file): ?>
                        <a href="<?php echo esc_url($edu->certificate_file); ?>" target="_blank" class="certification-preview-btn" data-filename="<?php echo esc_attr($edu->school); ?>" style="margin-right: 10px;">
                            <i class="fas fa-graduation-cap"></i> 졸업/재학증명서 보기
                        </a>
                    <?php endif; ?>
                    <?php if (isset($edu->transcript_file) && $edu->transcript_file): ?>
                        <a href="<?php echo esc_url($edu->transcript_file); ?>" target="_blank" class="certification-preview-btn" data-filename="<?php echo esc_attr($edu->school . ' 성적증명서'); ?>">
                            <i class="fas fa-file-alt"></i> 성적증명서 보기
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- 자격증 섹션 -->
    <?php if (!empty($certifications)): ?>
    <section class="resume-section">
        <h2>자격증</h2>
        
        <?php foreach ($certifications as $cert): ?>
            <div class="certification-item">
                <h3 class="certification-name"><?php echo esc_html($cert->name); ?></h3>
                <div class="certification-details">
                    <?php if ($cert->certificate_number): ?>
                        <?php echo esc_html($cert->certificate_number); ?>
                    <?php endif; ?>
                    <?php if ($cert->score): ?>
                        - <?php echo esc_html($cert->score); ?>
                    <?php endif; ?>
                    <?php if ($cert->organization): ?>
                        <br><small>발급기관: <?php echo esc_html($cert->organization); ?></small>
                    <?php endif; ?>
                    <?php if (isset($cert->issue_date) && $cert->issue_date): ?>
                        <br><small>취득일: <?php echo esc_html(date('Y.m.d', strtotime($cert->issue_date))); ?></small>
                    <?php endif; ?>
                </div>
                
                <?php if ($cert->certificate_file): ?>
                    <div class="certification-file">
                        <a href="<?php echo esc_url($cert->certificate_file); ?>" target="_blank" class="certification-preview-btn" data-filename="<?php echo esc_attr($cert->name); ?>">
                            <i class="fas fa-certificate"></i> 자격증 보기
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- 교육 내용 섹션 -->
    <?php if (!empty($trainings)): ?>
    <section class="resume-section">
        <h2>교육 내용</h2>
        
        <?php foreach ($trainings as $training): ?>
            <div class="training-item">
                <h3 class="training-title"><?php echo esc_html($training->title); ?></h3>
                <div class="training-organization"><?php echo esc_html($training->organization); ?></div>
                <div class="training-period">
                    <?php 
                    if ($training->start_date) {
                        echo esc_html(date('Y.m.d', strtotime($training->start_date)));
                        if ($training->end_date) {
                            echo ' ~ ' . esc_html(date('Y.m.d', strtotime($training->end_date)));
                        }
                    }
                    ?>
                </div>
                
                <?php if ($training->description): ?>
                    <div class="training-description"><?php echo wp_kses_post($training->description); ?></div>
                <?php endif; ?>
                
                <?php if ($training->skills_learned): ?>
                    <div class="training-skills">
                        <strong>습득 기술:</strong> <?php echo esc_html($training->skills_learned); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($training->certificate_file): ?>
                    <div class="training-file">
                        <a href="<?php echo esc_url($training->certificate_file); ?>" target="_blank" class="certification-preview-btn" data-filename="<?php echo esc_attr($training->title); ?>">
                            <i class="fas fa-award"></i> 수료증 보기
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- 수상 이력 섹션 -->
    <?php if (!empty($awards)): ?>
    <section class="resume-section">
        <h2>수상 이력</h2>
        <?php foreach ($awards as $award): ?>
        <div class="training-item award-item">
            <h3 class="award-title">
                <?php echo esc_html($award->title); ?>
                <?php if ($award->organization) echo ' - ' . esc_html($award->organization); ?>
            </h3>
            <div class="award-date">
                <?php if ($award->award_date) echo esc_html(date('Y.m.d', strtotime($award->award_date))); ?>
            </div>
            <?php if ($award->description): ?>
            <div class="award-description"><?php echo wp_kses_post($award->description); ?></div>
            <?php endif; ?>
            <?php if ($award->certificate_file): ?>
            <div class="award-file">
                <a href="<?php echo esc_url($award->certificate_file); ?>" target="_blank" class="certification-preview-btn" data-filename="<?php echo esc_attr($award->title); ?>">
                    <i class="fas fa-file-alt"></i> 상장 보기
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>

<?php get_footer(); ?>