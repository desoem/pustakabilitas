<?php
/**
 * Template for displaying book archive
 */

get_header();
?>

<div class="pustakabilitas-archive-container">
    <header class="archive-header">
        <h1 class="archive-title">
            <?php
            if (is_search()) {
                printf(__('Search Results for: %s', 'pustakabilitas'), get_search_query());
            } else {
                _e('Library Books', 'pustakabilitas');
            }
            ?>
        </h1>
    </header>

    <?php if (have_posts()) : ?>
        <div class="pustakabilitas-books-grid standard-grid">
            <?php while (have_posts()) : the_post(); 
                include PUSTAKABILITAS_PLUGIN_DIR . 'templates/partials/book-card.php';
            endwhile; ?>
        </div>

        <?php pustakabilitas_pagination(); ?>
    <?php else : ?>
        <p class="no-books"><?php _e('No books found.', 'pustakabilitas'); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
