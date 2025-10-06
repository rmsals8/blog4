<?php
// 교육 관리자 페이지
function resume_education_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $message = '';
    
    // POST 데이터 처리
    if (isset($_POST['submit_education'])) {
        // 보안 체크
        check_admin_referer('resume_education_nonce');
        
        $education = array();
        if (isset($_POST['education_school']) && is_array($_POST['education_school'])) {
            foreach ($_POST['education_school'] as $index => $school) {
                if (!empty($school)) {
                    $education[] = array(
                        'school' => sanitize_text_field($school),
                        'degree' => sanitize_text_field($_POST['education_degree'][$index]),
                        'major' => sanitize_text_field($_POST['education_major'][$index]),
                        'period' => sanitize_text_field($_POST['education_period'][$index]),
                        'description' => wp_kses_post($_POST['education_description'][$index])
                    );
                }
            }
        }
        update_option('resume_education', $education);
        
        // 교육 관련 파일 처리
        if (isset($_FILES['education_file'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            
            foreach ($_FILES['education_file']['name'] as $index => $value) {
                if ($_FILES['education_file']['size'][$index] > 0) {
                    $_FILES['education_file_upload'] = array(
                        'name' => $_FILES['education_file']['name'][$index],
                        'type' => $_FILES['education_file']['type'][$index],
                        'tmp_name' => $_FILES['education_file']['tmp_name'][$index],
                        'error' => $_FILES['education_file']['error'][$index],
                        'size' => $_FILES['education_file']['size'][$index]
                    );
                    
                    $attachment_id = media_handle_upload('education_file_upload', 0);
                    
                    if (!is_wp_error($attachment_id)) {
                        $education[$index]['file_id'] = $attachment_id;
                    }
                } elseif (isset($_POST['education_file_id'][$index])) {
                    $education[$index]['file_id'] = intval($_POST['education_file_id'][$index]);
                }
            }
            update_option('resume_education', $education);
        }
        
        $message = '<div class="notice notice-success"><p>교육 정보가 성공적으로 저장되었습니다.</p></div>';
    }
    
    // 저장된 데이터 불러오기
    $education = get_option('resume_education', array());
    
    ?>
    <div class="wrap">
        <h1>교육 관리</h1>
        <?php echo $message; ?>
        
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('resume_education_nonce'); ?>
            
            <div id="education-container">
                <?php foreach ($education as $index => $edu): ?>
                    <div class="education-item">
                        <h3>교육 #<?php echo $index + 1; ?></h3>
                        <table class="form-table">
                            <tr>
                                <th><label for="education_school_<?php echo $index; ?>">학교/기관명</label></th>
                                <td><input type="text" name="education_school[]" id="education_school_<?php echo $index; ?>" value="<?php echo esc_attr($edu['school']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="education_degree_<?php echo $index; ?>">학위</label></th>
                                <td><input type="text" name="education_degree[]" id="education_degree_<?php echo $index; ?>" value="<?php echo esc_attr($edu['degree']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="education_major_<?php echo $index; ?>">전공</label></th>
                                <td><input type="text" name="education_major[]" id="education_major_<?php echo $index; ?>" value="<?php echo esc_attr($edu['major']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="education_period_<?php echo $index; ?>">기간</label></th>
                                <td><input type="text" name="education_period[]" id="education_period_<?php echo $index; ?>" value="<?php echo esc_attr($edu['period']); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="education_description_<?php echo $index; ?>">설명</label></th>
                                <td><textarea name="education_description[]" id="education_description_<?php echo $index; ?>" rows="5" class="large-text"><?php echo esc_textarea($edu['description']); ?></textarea></td>
                            </tr>
                            <tr>
                                <th><label for="education_file_<?php echo $index; ?>">관련 파일</label></th>
                                <td>
                                    <div class="file-upload-container">
                                        <input type="file" name="education_file[]" id="education_file_<?php echo $index; ?>">
                                        <?php if (isset($edu['file_id'])): ?>
                                            <input type="hidden" name="education_file_id[]" value="<?php echo esc_attr($edu['file_id']); ?>">
                                            <div class="file-preview">
                                                <?php
                                                $file_url = wp_get_attachment_url($edu['file_id']);
                                                $file_type = wp_check_filetype(get_attached_file($edu['file_id']));
                                                if (strpos($file_type['type'], 'image') !== false) {
                                                    echo wp_get_attachment_image($edu['file_id'], 'thumbnail');
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
                        <button type="button" class="button remove-education" onclick="removeEducation(this)">교육 삭제</button>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button" onclick="addEducation()">교육 추가</button>
            <?php submit_button('저장', 'primary', 'submit_education'); ?>
        </form>
        
        <script>
        function addEducation() {
            var container = document.getElementById('education-container');
            var index = container.children.length;
            var educationItem = document.createElement('div');
            educationItem.className = 'education-item';
            educationItem.innerHTML = `
                <h3>교육 #${index + 1}</h3>
                <table class="form-table">
                    <tr>
                        <th><label for="education_school_${index}">학교/기관명</label></th>
                        <td><input type="text" name="education_school[]" id="education_school_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="education_degree_${index}">학위</label></th>
                        <td><input type="text" name="education_degree[]" id="education_degree_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="education_major_${index}">전공</label></th>
                        <td><input type="text" name="education_major[]" id="education_major_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="education_period_${index}">기간</label></th>
                        <td><input type="text" name="education_period[]" id="education_period_${index}" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="education_description_${index}">설명</label></th>
                        <td><textarea name="education_description[]" id="education_description_${index}" rows="5" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="education_file_${index}">관련 파일</label></th>
                        <td>
                            <div class="file-upload-container">
                                <input type="file" name="education_file[]" id="education_file_${index}">
                            </div>
                        </td>
                    </tr>
                </table>
                <button type="button" class="button remove-education" onclick="removeEducation(this)">교육 삭제</button>
                <hr>
            `;
            container.appendChild(educationItem);
        }
        
        function removeEducation(button) {
            if (confirm('이 교육 정보를 삭제하시겠습니까?')) {
                button.parentElement.remove();
                updateEducationNumbers();
            }
        }
        
        function updateEducationNumbers() {
            var educations = document.querySelectorAll('.education-item');
            educations.forEach(function(education, index) {
                education.querySelector('h3').textContent = `교육 #${index + 1}`;
            });
        }
        </script>
    </div>
    <?php
}
?> 