<?php
if (!defined('ABSPATH')) exit;

function resume_awards_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'resume_awards';
    // 테이블 생성 (최초 1회)
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        organization varchar(255) DEFAULT '' NOT NULL,
        award_date date DEFAULT NULL,
        description text DEFAULT NULL,
        certificate_file varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // 처리
    if (isset($_POST['save_award'])) {
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'organization' => sanitize_text_field($_POST['organization']),
            'award_date' => sanitize_text_field($_POST['award_date']),
            'description' => sanitize_textarea_field($_POST['description'])
        );
        // 파일
        if (!empty($_FILES['certificate_file']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $movefile = wp_handle_upload($_FILES['certificate_file'], array('test_form'=>false));
            if (isset($movefile['url'])) $data['certificate_file'] = $movefile['url'];
        }
        if (!empty($_POST['award_id'])) {
            $wpdb->update($table,$data,array('id'=>intval($_POST['award_id'])));
        } else {
            $wpdb->insert($table,$data);
        }
    }
    if (isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
        $wpdb->delete($table,array('id'=>intval($_GET['id'])));
    }

    $edit_award=null;
    if (isset($_GET['action']) && $_GET['action']==='edit' && isset($_GET['id'])) {
        $edit_award = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",intval($_GET['id'])));
    }
    $awards = $wpdb->get_results("SELECT * FROM $table ORDER BY award_date DESC");
    ?>
    <div class="wrap"><h1>수상 이력 관리</h1>
    <form method="post" enctype="multipart/form-data">
        <?php if($edit_award): ?>
            <input type="hidden" name="award_id" value="<?php echo esc_attr($edit_award->id);?>">
        <?php endif; ?>
        <table class="form-table">
            <tr><th><label for="title">수상명 *</label></th><td><input type="text" name="title" id="title" class="regular-text" value="<?php echo $edit_award?esc_attr($edit_award->title):''; ?>" required></td></tr>
            <tr><th><label for="organization">기관</label></th><td><input type="text" name="organization" id="organization" class="regular-text" value="<?php echo $edit_award?esc_attr($edit_award->organization):''; ?>"></td></tr>
            <tr><th><label for="award_date">수상일</label></th><td><input type="date" name="award_date" id="award_date" value="<?php echo $edit_award?esc_attr($edit_award->award_date):''; ?>"></td></tr>
            <tr><th><label for="description">설명</label></th><td><textarea name="description" id="description" rows="3" class="large-text"><?php echo $edit_award?esc_textarea($edit_award->description):''; ?></textarea></td></tr>
            <tr><th><label for="certificate_file">증명 파일</label></th><td><input type="file" name="certificate_file" id="certificate_file">
            <?php if($edit_award && $edit_award->certificate_file): ?><br><a href="<?php echo esc_url($edit_award->certificate_file); ?>" target="_blank">현재 파일</a><?php endif; ?></td></tr>
        </table>
        <p class="submit"><input type="submit" name="save_award" class="button-primary" value="<?php echo $edit_award?'수정':'추가';?>"></p>
    </form>

    <h2>목록</h2>
    <table class="widefat fixed striped">
        <thead><tr><th>수상명</th><th>기관</th><th>수상일</th><th>작업</th></tr></thead><tbody>
        <?php if($awards): foreach($awards as $a): ?>
            <tr><td><?php echo esc_html($a->title);?></td><td><?php echo esc_html($a->organization);?></td><td><?php echo esc_html($a->award_date);?></td>
            <td><a href="?page=resume-awards&action=edit&id=<?php echo $a->id;?>" class="button">수정</a> <a href="?page=resume-awards&action=delete&id=<?php echo $a->id;?>" onclick="return confirm('삭제?')" class="button">삭제</a></td></tr>
        <?php endforeach; else: ?><tr><td colspan="4">없음</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php
}
// End of file without closing PHP tag to prevent accidental output 