<?php
// 주요활동 관리자 페이지
function resume_activities_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $message = '';
    
    // POST 데이터 처리
    if (isset($_POST['submit_activities'])) {
        // 보안 체크
        check_admin_referer('resume_activities_nonce');
        
        $activities = array();
        if (isset($_POST['activity_title']) && is_array($_POST['activity_title'])) {
            foreach ($_POST['activity_title'] as $index => $title) {
                if (!empty($title)) {
                    $activities[] = array(
                        'title' => sanitize_text_field($title),
                        'organization' => sanitize_text_field($_POST['activity_organization'][$index]),
                        'period' => sanitize_text_field($_POST['activity_period'][$index]),
                        'role' => sanitize_text_field($_POST['activity_role'][$index]),
                        'description' => wp_kses_post($_POST['activity_description'][$index])
                    );
                }
            }
        }
        update_option('resume_activities', $activities);
        
        // 활동 관련 파일 처리
        if (isset($_FILES['activity_file'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            
            foreach ($_FILES['activity_file']['name'] as $index => $value) {
                if ($_FILES['activity_file']['size'][$index] > 0) {
                    $_FILES['activity_file_upload'] = array(
                        'name' => $_FILES['activity_file']['name'][$index],
                        'type' => $_FILES['activity_file']['type'][$index],
                        'tmp_name' => $_FILES['activity_file']['tmp_name'][$index],
                        'error' => $_FILES['activity_file']['error'][$index],
                        'size' => $_FILES['activity_file']['size'][$index]
                    );
                    
                    $attachment_id = media_handle_upload('activity_file_upload', 0);
                    
                    if (!is_wp_error($attachment_id)) {
                        $activities[$index]['file_id'] = $attachment_id;
                    }
                } elseif (isset($_POST['activity_file_id'][$index])) {
                    $activities[$index]['file_id'] = intval($_POST['activity_file_id'][$index]);
                }
            }
            update_option('resume_activities', $activities);
        }
        
        $message = '<div class="notice notice-success"><p>주요활동 정보가 성공적으로 저장되었습니다.</p></div>';
    }
    
    // 저장된 데이터 불러오기
    $activities = get_option('resume_activities', array());
    
    ?>
    <div class="wrap">
        <h1>주요활동 관리</h1>
        <?php echo $message; ?>
        
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('resume_activities_nonce'); ?>
            
            <div id="activities-container">
                <?php foreach ($activities as $index => $activity): ?>
                    <div class="activity-item">
                        <h3>활동 #<?php echo $index + 1; ?></h3>
                        <table class="form-table">
                            <tr>
                                <th><label for="activity_title_<?php echo $index; ?>">활동명</label></th>
                                <td><input type="text" name="activity_title[]" id="activity_title_<?php echo $index; ?>" value="<?php echo esc_attr($activity['title']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="activity_organization_<?php echo $index; ?>">기관/단체</label></th>
                                <td><input type="text" name="activity_organization[]" id="activity_organization_<?php echo $index; ?>" value="<?php echo esc_attr($activity['organization']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="activity_period_<?php echo $index; ?>">기간</label></th>
                                <td><input type="text" name="activity_period[]" id="activity_period_<?php echo $index; ?>" value="<?php echo esc_attr($activity['period']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="activity_role_<?php echo $index; ?>">역할</label></th>
                                <td><input type="text" name="activity_role[]" id="activity_role_<?php echo $index; ?>" value="<?php echo esc_attr($activity['role']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="activity_description_<?php echo $index; ?>">설명</label></th>
                                <td><textarea name="activity_description[]" id="activity_description_<?php echo $index; ?>" rows="5" class="large-text"><?php echo esc_textarea($activity['description']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th><label for="activity_file_<?php echo $index; ?>">관련 파일</label></th>
                                <td>
                                    <div class="file-upload-container">
                                        <input type="file" name="activity_file[]" id="activity_file_<?php echo $index; ?>">
                                        <?php if (isset($activity['file_id'])): ?>
                                            <input type="hidden" name="activity_file_id[]" value="<?php echo esc_attr($activity['file_id']); ?>">
                                            <div class="file-preview">
                                                <?php
                                                $file_url = wp_get_attachment_url($activity['file_id']);
                                                $file_type = wp_check_filetype(get_attached_file($activity['file_id']));
                                                if (strpos($file_type['type'], 'image') !== false) {
                                                    echo wp_get_attachment_image($activity['file_id'], 'thumbnail');
                                                } else {
                                                    echo '<a href="' . esc_url($file_url) . '" target="_blank">' . basename($file_url) . '</a>';
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="button remove-activity" onclick="removeActivity(this)">활동 삭제</button>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button" onclick="addActivity()">활동 추가</button>
            <?php submit_button('저장', 'primary', 'submit_activities'); ?>
        </form>
        
        <script>
        function addActivity() {
            var container = document.getElementById('activities-container');
            var index = container.children.length;
            var activityItem = document.createElement('div');
            activityItem.className = 'activity-item';
            activityItem.innerHTML = `
                <h3>활동 #${index + 1}</h3>
                <table class="form-table">
                    <tr>
                        <th><label for="activity_title_${index}">활동명</label></th>
                        <td><input type="text" name="activity_title[]" id="activity_title_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="activity_organization_${index}">기관/단체</label></th>
                        <td><input type="text" name="activity_organization[]" id="activity_organization_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="activity_period_${index}">기간</label></th>
                        <td><input type="text" name="activity_period[]" id="activity_period_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="activity_role_${index}">역할</label></th>
                        <td><input type="text" name="activity_role[]" id="activity_role_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="activity_description_${index}">설명</label></th>
                        <td><textarea name="activity_description[]" id="activity_description_${index}" rows="5" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="activity_file_${index}">관련 파일</label></th>
                        <td>
                            <div class="file-upload-container">
                                <input type="file" name="activity_file[]" id="activity_file_${index}">
                            </div>
                        </td>
                    </tr>
                </table>
                <button type="button" class="button remove-activity" onclick="removeActivity(this)">활동 삭제</button>
                <hr>
            `;
            container.appendChild(activityItem);
        }
        
        function removeActivity(button) {
            if (confirm('이 활동 정보를 삭제하시겠습니까?')) {
                button.parentElement.remove();
                updateActivityNumbers();
            }
        }
        
        function updateActivityNumbers() {
            var activities = document.querySelectorAll('.activity-item');
            activities.forEach(function(activity, index) {
                activity.querySelector('h3').textContent = `활동 #${index + 1}`;
            });
        }
        </script>
    </div>
    <?php
}
?> 