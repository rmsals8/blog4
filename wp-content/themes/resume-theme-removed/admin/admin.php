<?php
if (!defined('ABSPATH')) exit;

// 공통 헤더 출력
function resume_admin_header($title) {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html($title); ?></h1>
    </div>
    <?php
}

// 저장 메시지 출력
function resume_admin_notice($message, $type = 'success') {
    ?>
    <div class="notice notice-<?php echo $type; ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php
} 