<?php
if (!defined('ABSPATH')) exit;

// 프로젝트 관리 페이지 함수
function resume_projects_page() {
    // 프로젝트 데이터 조회
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_projects';
    
    // demo_url_label 컬럼 확인 후 없으면 추가
    $columns = $wpdb->get_col("DESCRIBE $table_name");
    if (!in_array('demo_url_label', $columns)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN demo_url_label VARCHAR(255) DEFAULT ''");
    }

    // 기본 라벨
    $demo_url_label = '실제 서비스 URL';

    // 프로젝트 데이터 저장 (신규 또는 수정)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
        if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
            // 수정
            $result = $wpdb->update(
                $table_name,
                array(
                    'title' => sanitize_text_field($_POST['title']),
                    'organization' => sanitize_text_field($_POST['organization']),
                    'description' => sanitize_textarea_field($_POST['description']),
                    'role' => sanitize_text_field($_POST['role']),
                    'technologies' => sanitize_textarea_field($_POST['technologies']),
                    'start_date' => sanitize_text_field($_POST['start_date']),
                    'end_date' => sanitize_text_field($_POST['end_date']),
                    'responsibilities' => sanitize_textarea_field($_POST['responsibilities']),
                    'achievements' => sanitize_textarea_field($_POST['achievements']),
                    'github_url' => esc_url_raw($_POST['github_url']),
                    'demo_url' => esc_url_raw($_POST['demo_url']),
                    'demo_url_label' => sanitize_text_field($_POST['demo_url_label'])
                ),
                array('id' => intval($_POST['project_id'])),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result !== false) {
                echo '<div class="updated"><p>프로젝트 정보가 수정되었습니다.</p></div>';
            } else {
                echo '<div class="error"><p>프로젝트 수정 중 오류가 발생했습니다.</p></div>';
            }
        } else {
            // 신규 추가
            $result = $wpdb->insert(
                $table_name,
                array(
                    'title' => sanitize_text_field($_POST['title']),
                    'organization' => sanitize_text_field($_POST['organization']),
                    'description' => sanitize_textarea_field($_POST['description']),
                    'role' => sanitize_text_field($_POST['role']),
                    'technologies' => sanitize_textarea_field($_POST['technologies']),
                    'start_date' => sanitize_text_field($_POST['start_date']),
                    'end_date' => sanitize_text_field($_POST['end_date']),
                    'responsibilities' => sanitize_textarea_field($_POST['responsibilities']),
                    'achievements' => sanitize_textarea_field($_POST['achievements']),
                    'github_url' => esc_url_raw($_POST['github_url']),
                    'demo_url' => esc_url_raw($_POST['demo_url']),
                    'demo_url_label' => sanitize_text_field($_POST['demo_url_label'])
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );

            if ($result !== false) {
                echo '<div class="updated"><p>프로젝트 정보가 저장되었습니다.</p></div>';
            } else {
                echo '<div class="error"><p>프로젝트 저장 중 오류가 발생했습니다.</p></div>';
            }
        }
    }

    // 프로젝트 삭제
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $wpdb->delete(
            $table_name,
            array('id' => intval($_GET['id'])),
            array('%d')
        );
        
        echo '<div class="updated"><p>프로젝트가 삭제되었습니다.</p></div>';
    }

    // 현재 저장된 데이터 조회
    $projects = $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_date DESC");
    
    // 수정할 프로젝트 데이터 조회
    $edit_project = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_project = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
    }

    // 편집 모드 또는 기본 입력값 반영
    if ($edit_project && isset($edit_project->demo_url_label) && $edit_project->demo_url_label !== '') {
        $demo_url_label = $edit_project->demo_url_label;
    } elseif (isset($_POST['demo_url_label'])) {
        $demo_url_label = sanitize_text_field($_POST['demo_url_label']);
    }
    
    ?>
    <div class="wrap">
        <h1>프로젝트 관리</h1>
        
        <form method="post" action="">
            <?php if ($edit_project): ?>
                <h2>프로젝트 수정</h2>
                <input type="hidden" name="project_id" value="<?php echo esc_attr($edit_project->id); ?>">
            <?php else: ?>
                <h2>새 프로젝트 추가</h2>
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="title">프로젝트명</label></th>
                    <td><input type="text" id="title" name="title" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->title) : ''; ?>" required></td>
                </tr>
                <tr>
                    <th><label for="organization">인력 구성</label></th>
                    <td><input type="text" id="organization" name="organization" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->organization) : ''; ?>" required></td>
                </tr>
                <tr>
                    <th><label for="role">역할</label></th>
                    <td><input type="text" id="role" name="role" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->role) : ''; ?>" required></td>
                </tr>
                <tr>
                    <th><label for="description">프로젝트 설명</label></th>
                    <td><textarea id="description" name="description" class="large-text" rows="5" required><?php echo $edit_project ? esc_textarea($edit_project->description) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="technologies">사용 기술</label></th>
                    <td>
                        <div id="tech-table-container">
                            <table class="tech-input-table">
                                <thead>
                                    <tr>
                                        <th style="width:25%">구분</th>
                                        <th style="width:60%">Skill</th>
                                        <th style="width:15%">작업</th>
                                    </tr>
                                </thead>
                                <tbody id="tech-table-body">
                                    <tr>
                                        <td><input type="text" class="tech-category" placeholder="구분" style="width:100%"></td>
                                        <td><input type="text" class="tech-skill" placeholder="기술스택" style="width:100%"></td>
                                        <td><button type="button" class="button remove-tech-row">삭제</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" id="add-tech-row" class="button">항목 추가</button>
                        </div>
                        <textarea id="technologies" name="technologies" style="display:none;"></textarea>
                        
                        <script>
                        jQuery(document).ready(function($) {
                            // 기존 데이터 로드
                            <?php if ($edit_project && $edit_project->technologies): ?>
                                var existingData = <?php echo json_encode($edit_project->technologies); ?>;
                                var lines = existingData.split('\n');
                                $('#tech-table-body').empty();
                                lines.forEach(function(line) {
                                    line = line.trim();
                                    if (line) {
                                        var parts = line.split(',');
                                        var category = parts.length > 1 ? parts[0].trim() : '';
                                        var skill = parts.length > 1 ? parts.slice(1).join(',').trim() : line;
                                        var newRow = '<tr>' +
                                            '<td><input type="text" class="tech-category" value="' + category + '" placeholder="구분" style="width:100%"></td>' +
                                            '<td><input type="text" class="tech-skill" value="' + skill + '" placeholder="기술스택" style="width:100%"></td>' +
                                            '<td><button type="button" class="button remove-tech-row">삭제</button></td>' +
                                            '</tr>';
                                        $('#tech-table-body').append(newRow);
                                    }
                                });
                                if ($('#tech-table-body tr').length === 0) {
                                    $('#tech-table-body').append('<tr><td><input type="text" class="tech-category" placeholder="구분" style="width:100%"></td><td><input type="text" class="tech-skill" placeholder="기술스택" style="width:100%"></td><td><button type="button" class="button remove-tech-row">삭제</button></td></tr>');
                                }
                            <?php endif; ?>
                            
                            // 항목 추가
                            $('#add-tech-row').click(function() {
                                var newRow = '<tr>' +
                                    '<td><input type="text" class="tech-category" placeholder="구분" style="width:100%"></td>' +
                                    '<td><input type="text" class="tech-skill" placeholder="기술스택" style="width:100%"></td>' +
                                    '<td><button type="button" class="button remove-tech-row">삭제</button></td>' +
                                    '</tr>';
                                $('#tech-table-body').append(newRow);
                            });
                            
                            // 항목 삭제
                            $(document).on('click', '.remove-tech-row', function() {
                                if ($('#tech-table-body tr').length > 1) {
                                    $(this).closest('tr').remove();
                                }
                            });
                            
                            // 폼 제출 전에 textarea에 데이터 저장
                            $('form').submit(function() {
                                var techData = [];
                                $('#tech-table-body tr').each(function() {
                                    var category = $(this).find('.tech-category').val().trim();
                                    var skill = $(this).find('.tech-skill').val().trim();
                                    if (category || skill) {
                                        if (category && skill) {
                                            techData.push(category + ', ' + skill);
                                        } else if (skill) {
                                            techData.push(skill);
                                        }
                                    }
                                });
                                $('#technologies').val(techData.join('\n'));
                            });
                        });
                        </script>
                        
                        <style>
                        .tech-input-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }
                        .tech-input-table th, .tech-input-table td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }
                        .tech-input-table th {
                            background-color: #f9f9f9;
                            font-weight: bold;
                        }
                        </style>
                    </td>
                </tr>
                <tr>
                    <th><label for="responsibilities">주요업무 및 상세역할</label></th>
                    <td><textarea id="responsibilities" name="responsibilities" class="large-text" rows="8"><?php echo $edit_project ? esc_textarea($edit_project->responsibilities) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="achievements">느낀 점</label></th>
                    <td><textarea id="achievements" name="achievements" class="large-text" rows="5"><?php echo $edit_project ? esc_textarea($edit_project->achievements) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="start_date">시작일</label></th>
                    <td><input type="date" id="start_date" name="start_date" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->start_date) : ''; ?>" required></td>
                </tr>
                <tr>
                    <th><label for="end_date">종료일</label></th>
                    <td><input type="date" id="end_date" name="end_date" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->end_date) : ''; ?>" required></td>
                </tr>
                <tr>
                    <th><label for="github_url">GitHub URL</label></th>
                    <td><input type="url" id="github_url" name="github_url" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->github_url) : ''; ?>"></td>
                </tr>
                <tr>
                    <th><label for="demo_url_label">Demo URL 열 이름</label></th>
                    <td><input type="text" id="demo_url_label" name="demo_url_label" class="regular-text" value="<?php echo esc_attr($demo_url_label); ?>"></td>
                </tr>
                <tr>
                    <th><label for="demo_url"><?php echo esc_html($demo_url_label); ?></label></th>
                    <td><input type="url" id="demo_url" name="demo_url" class="regular-text" value="<?php echo $edit_project ? esc_attr($edit_project->demo_url) : ''; ?>"></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_project" class="button button-primary" value="<?php echo $edit_project ? '프로젝트 수정' : '프로젝트 추가'; ?>">
                <?php if ($edit_project): ?>
                    <a href="?page=resume-projects" class="button">취소</a>
                <?php endif; ?>
            </p>
        </form>

        <h2>등록된 프로젝트 목록</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>프로젝트명</th>
                    <th>인력 구성</th>
                    <th>역할</th>
                    <th>기간</th>
                    <th>GitHub</th>
                    <th>작업</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo esc_html($project->title); ?></td>
                        <td><?php echo esc_html($project->organization); ?></td>
                        <td><?php echo esc_html($project->role); ?></td>
                        <td><?php echo esc_html($project->start_date); ?> ~ <?php echo esc_html($project->end_date); ?></td>
                        <td>
                            <?php if (!empty($project->github_url)): ?>
                            <a href="<?php echo esc_url($project->github_url); ?>" target="_blank">보기</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=resume-projects&action=edit&id=<?php echo $project->id; ?>" 
                               class="button button-small">수정</a>
                            <a href="?page=resume-projects&action=delete&id=<?php echo $project->id; ?>" 
                               onclick="return confirm('정말로 삭제하시겠습니까?')" 
                               class="button button-small">삭제</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6">등록된 프로젝트가 없습니다.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// 프로젝트 데이터 조회 (프론트엔드용)
function get_projects_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_projects';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_date DESC");
    return $results ? $results : array();
} 