<?php
/**
 * Template part for displaying book card
 */
?>
<div class="book-card">
    <div class="book-thumbnail">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium'); ?>
        <?php else : ?>
            <img src="<?php echo PUSTAKABILITAS_PLUGIN_URL; ?>assets/images/no-cover.jpg" alt="<?php _e('No Cover', 'pustakabilitas'); ?>">
        <?php endif; ?>
    </div>
    
    <div class="book-details">
        <h3 class="book-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        
        <?php
        $author = get_post_meta(get_the_ID(), '_pustakabilitas_book_author', true);
        if ($author) : ?>
            <div class="book-author">
                <span class="author-label"><?php _e('By:', 'pustakabilitas'); ?></span>
                <span class="author-name"><?php echo esc_html($author); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="book-meta">
            <?php
            $download_count = get_post_meta(get_the_ID(), '_pustakabilitas_download_count', true);
            $read_count = get_post_meta(get_the_ID(), '_pustakabilitas_read_count', true);
            ?>
            <span class="downloads">
                <i class="eicon-download-circle-o"></i>
                <?php echo number_format_i18n($download_count ? $download_count : 0); ?>
            </span>
            <span class="reads">
                <i class="eicon-preview-medium"></i>
                <?php echo number_format_i18n($read_count ? $read_count : 0); ?>
            </span>
        </div>

        <div class="book-actions">
            <a href="<?php the_permalink(); ?>" class="read-book-btn">
                <?php _e('Read Book', 'pustakabilitas'); ?>
            </a>
        </div>
    </div>
</div> 