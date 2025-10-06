<?php
if (!defined('ABSPATH')) exit;

$certifications = get_resume_certifications();
?>

<div class="wrap">
    <h1>자격증 관리</h1>
    <form method="post" action="" class="admin-form" enctype="multipart/form-data">
        <?php wp_nonce_field('save_resume_certifications', 'resume_certifications_nonce'); ?>
        
        <div id="certifications-container">
            <?php if ($certifications): foreach ($certifications as $index => $cert): ?>
                <div class="certification-item">
                    <h3>자격증 #<?php echo $index + 1; ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="cert_name_<?php echo $index; ?>">자격증명</label></th>
                            <td>
                                <input type="text" id="cert_name_<?php echo $index; ?>" name="certifications[<?php echo $index; ?>][name]" value="<?php echo esc_attr($cert->name); ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="cert_number_<?php echo $index; ?>">자격증 번호</label></th>
                            <td>
                                <input type="text" id="cert_number_<?php echo $index; ?>" name="certifications[<?php echo $index; ?>][number]" value="<?php echo esc_attr($cert->number); ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="cert_date_<?php echo $index; ?>">취득일</label></th>
                            <td>
                                <input type="date" id="cert_date_<?php echo $index; ?>" name="certifications[<?php echo $index; ?>][date]" value="<?php echo esc_attr($cert->date); ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="cert_file_<?php echo $index; ?>">자격증 파일</label></th>
                            <td>
                                <div class="file-upload-field">
                                    <?php if ($cert->file): ?>
                                        <div class="current-file">
                                            <a href="<?php echo esc_url($cert->file); ?>" class="file-preview" data-type="<?php echo wp_check_filetype($cert->file)['type']; ?>">
                                                현재 파일 보기
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" id="cert_file_<?php echo $index; ?>" name="certifications[<?php echo $index; ?>][file]" class="cert-file-upload" data-preview="cert-preview-<?php echo $index; ?>" accept=".pdf,.jpg,.jpeg,.png">
                                    <div id="cert-preview-<?php echo $index; ?>" class="file-upload-preview"></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p class="remove-item">
                        <button type="button" class="button button-link-delete remove-certification">자격증 삭제</button>
                    </p>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <p class="submit">
            <button type="button" id="add-certification" class="button">자격증 추가</button>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="저장">
        </p>
    </form>
</div>

<!-- 파일 미리보기 모달 -->
<div class="file-preview-container">
    <div class="file-preview-content">
        <div class="file-preview-close">&times;</div>
        <div class="file-preview-body"></div>
    </div>
</div>

<script type="text/template" id="certification-template">
    <div class="certification-item">
        <h3>자격증 #{index}</h3>
        <table class="form-table">
            <tr>
                <th><label for="cert_name_{index}">자격증명</label></th>
                <td>
                    <input type="text" id="cert_name_{index}" name="certifications[{index}][name]" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="cert_number_{index}">자격증 번호</label></th>
                <td>
                    <input type="text" id="cert_number_{index}" name="certifications[{index}][number]" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="cert_date_{index}">취득일</label></th>
                <td>
                    <input type="date" id="cert_date_{index}" name="certifications[{index}][date]" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="cert_file_{index}">자격증 파일</label></th>
                <td>
                    <div class="file-upload-field">
                        <input type="file" id="cert_file_{index}" name="certifications[{index}][file]" class="cert-file-upload" data-preview="cert-preview-{index}" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="cert-preview-{index}" class="file-upload-preview"></div>
                    </div>
                </td>
            </tr>
        </table>
        <p class="remove-item">
            <button type="button" class="button button-link-delete remove-certification">자격증 삭제</button>
        </p>
    </div>
</script>

<script>
jQuery(document).ready(function($) {
    let certIndex = <?php echo $certifications ? count($certifications) : 0; ?>;

    // 자격증 추가
    $('#add-certification').on('click', function() {
        const template = $('#certification-template').html();
        const newItem = template.replace(/{index}/g, certIndex++);
        $('#certifications-container').append(newItem);
    });

    // 자격증 삭제
    $(document).on('click', '.remove-certification', function() {
        $(this).closest('.certification-item').remove();
    });
});
</script> 