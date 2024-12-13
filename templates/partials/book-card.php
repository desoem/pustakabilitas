<article class="book-card">
    <div class="book-thumbnail">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium'); ?>
        <?php else : ?>
            <div class="no-thumbnail">
                <span class="dashicons dashicons-book"></span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="book-details">
        <h2 class="book-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>
        
        <div class="book-meta">
            <?php
            $author = get_post_meta(get_the_ID(), '_pustakabilitas_author', true);
            $publisher = get_post_meta(get_the_ID(), '_pustakabilitas_publisher', true);
            ?>
            <?php if ($author) : ?>
                <span class="book-author"><?php echo esc_html($author); ?></span>
            <?php endif; ?>
            
            <?php if ($publisher) : ?>
                <span class="book-publisher"><?php echo esc_html($publisher); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="book-excerpt">
            <?php the_excerpt(); ?>
        </div>
        
        <a href="<?php the_permalink(); ?>" class="read-more">
            <?php _e('View Details', 'pustakabilitas'); ?>
        </a>
    </div>
</article> 