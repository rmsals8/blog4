<?php
if (!defined('ABSPATH')) exit;

// 기술 스택 관리 페이지 함수
function resume_skills_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_skills';
    
    // 기술 스택 데이터 저장
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_skills'])) {
        // 기존 데이터 삭제
        $wpdb->query("DELETE FROM $table_name");
        
        // experienced skills 처리
        if (isset($_POST['experienced_category']) && isset($_POST['experienced_name'])) {
            $categories = $_POST['experienced_category'];
            $names = $_POST['experienced_name'];
            
            for ($i = 0; $i < count($categories); $i++) {
                if (!empty($categories[$i]) && !empty($names[$i])) {
                    $wpdb->insert(
                        $table_name,
                        array(
                            'category' => sanitize_text_field($categories[$i]),
                            'name' => sanitize_text_field($names[$i]),
                            'experience_type' => 'experienced'
                        )
                    );
                }
            }
        }
        
        // theoretical skills 처리
        if (isset($_POST['theoretical_category']) && isset($_POST['theoretical_name'])) {
            $categories = $_POST['theoretical_category'];
            $names = $_POST['theoretical_name'];
            
            for ($i = 0; $i < count($categories); $i++) {
                if (!empty($categories[$i]) && !empty($names[$i])) {
                    $wpdb->insert(
                        $table_name,
                        array(
                            'category' => sanitize_text_field($categories[$i]),
                            'name' => sanitize_text_field($names[$i]),
                            'experience_type' => 'theoretical'
                        )
                    );
                }
            }
        }
        
        echo '<div class="updated"><p>기술 스택 정보가 저장되었습니다.</p></div>';
    }

    // 데이터 조회
    $skills = $wpdb->get_results("SELECT * FROM $table_name ORDER BY experience_type, category, id ASC");
    $experienced_skills = array_filter($skills, function($skill) {
        return $skill->experience_type === 'experienced';
    });
    $theoretical_skills = array_filter($skills, function($skill) {
        return $skill->experience_type === 'theoretical';
    });
    
    ?>
    <div class="wrap">
        <h1>기술 스택 관리</h1>
        <form method="post" action="">
            <h2>사용 경험이 있는 Skill Set</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>구분</th>
                        <th>Skill</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody id="experienced-skills">
                    <?php foreach ($experienced_skills as $skill): ?>
                    <tr>
                        <td>
                            <input type="text" name="experienced_category[]" value="<?php echo esc_attr($skill->category); ?>" required>
                        </td>
                        <td>
                            <input type="text" name="experienced_name[]" value="<?php echo esc_attr($skill->name); ?>" required>
                        </td>
                        <td>
                            <button type="button" class="button remove-skill">삭제</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>
                            <input type="text" name="experienced_category[]" placeholder="구분">
                        </td>
                        <td>
                            <input type="text" name="experienced_name[]" placeholder="기술명">
                        </td>
                        <td>
                            <button type="button" class="button remove-skill">삭제</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="button add-experienced-skill">기술 추가</button>

            <h2>사용 경험은 없으나, 이론적 지식이 있는 Skill Set</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>구분</th>
                        <th>Skill</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody id="theoretical-skills">
                    <?php foreach ($theoretical_skills as $skill): ?>
                    <tr>
                        <td>
                            <input type="text" name="theoretical_category[]" value="<?php echo esc_attr($skill->category); ?>" required>
                        </td>
                        <td>
                            <input type="text" name="theoretical_name[]" value="<?php echo esc_attr($skill->name); ?>" required>
                        </td>
                        <td>
                            <button type="button" class="button remove-skill">삭제</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>
                            <input type="text" name="theoretical_category[]" placeholder="구분">
                        </td>
                        <td>
                            <input type="text" name="theoretical_name[]" placeholder="기술명">
                        </td>
                        <td>
                            <button type="button" class="button remove-skill">삭제</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="button add-theoretical-skill">기술 추가</button>

            <p class="submit">
                <input type="submit" name="save_skills" class="button button-primary" value="저장">
            </p>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // 경험 기술 추가
        $('.add-experienced-skill').click(function() {
            var row = `
                <tr>
                    <td><input type="text" name="experienced_category[]" placeholder="구분"></td>
                    <td><input type="text" name="experienced_name[]" placeholder="기술명"></td>
                    <td><button type="button" class="button remove-skill">삭제</button></td>
                </tr>
            `;
            $('#experienced-skills').append(row);
        });

        // 이론 기술 추가
        $('.add-theoretical-skill').click(function() {
            var row = `
                <tr>
                    <td><input type="text" name="theoretical_category[]" placeholder="구분"></td>
                    <td><input type="text" name="theoretical_name[]" placeholder="기술명"></td>
                    <td><button type="button" class="button remove-skill">삭제</button></td>
                </tr>
            `;
            $('#theoretical-skills').append(row);
        });

        // 기술 삭제
        $(document).on('click', '.remove-skill', function() {
            $(this).closest('tr').remove();
        });
    });
    </script>
    <?php
}

// 기술 스택 데이터 조회 (프론트엔드용)
function get_skills_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'resume_skills';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY experience_type, category, id ASC");
    return $results ? $results : array();
}