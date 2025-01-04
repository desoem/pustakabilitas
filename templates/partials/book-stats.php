<?php
$book_id = get_the_ID();
$stats = array(
    'downloads' => get_post_meta($book_id, '_pustakabilitas_download_count', true) ?: 0,
    'reads' => get_post_meta($book_id, '_pustakabilitas_read_count', true) ?: 0,
    'rating' => get_post_meta($book_id, '_pustakabilitas_rating', true) ?: 0
);
?>
<div class="book-stats">
    <div class="stat-item downloads">
        <span class="stat-number"><?php echo number_format_i18n($stats['downloads']); ?></span>
        <span class="stat-label"><?php _e('Downloads', 'pustakabilitas'); ?></span>
    </div>
    <div class="stat-item reads">
        <span class="stat-number"><?php echo number_format_i18n($stats['reads']); ?></span>
        <span class="stat-label"><?php _e('Reads', 'pustakabilitas'); ?></span>
    </div>
</div>
