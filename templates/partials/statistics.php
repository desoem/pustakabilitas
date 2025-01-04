<?php
/**
 * Template part for displaying statistics
 */
?>
<div class="pustakabilitas-statistics">
    <div class="stat-item">
        <div class="stat-icon">
            <i class="eicon-library-open"></i>
        </div>
        <span class="stat-number"><?php echo number_format_i18n($total_books); ?></span>
        <span class="stat-label"><?php _e('Books', 'pustakabilitas'); ?></span>
    </div>
    
    <div class="stat-item">
        <div class="stat-icon">
            <i class="eicon-user-circle-o"></i>
        </div>
        <span class="stat-number"><?php echo number_format_i18n($total_users); ?></span>
        <span class="stat-label"><?php _e('Users', 'pustakabilitas'); ?></span>
    </div>
    
    <div class="stat-item">
        <div class="stat-icon">
            <i class="eicon-preview-medium"></i>
        </div>
        <span class="stat-number"><?php echo number_format_i18n($total_reads); ?></span>
        <span class="stat-label"><?php _e('Reads', 'pustakabilitas'); ?></span>
    </div>
    
    <div class="stat-item">
        <div class="stat-icon">
            <i class="eicon-download-circle-o"></i>
        </div>
        <span class="stat-number"><?php echo number_format_i18n($total_downloads); ?></span>
        <span class="stat-label"><?php _e('Downloads', 'pustakabilitas'); ?></span>
    </div>
</div> 