<?php
/**
 * Template part for displaying books grid
 */

// Menggunakan query yang dikirim dari shortcode
$books_query = isset($latest_books) ? $latest_books : (isset($popular_books) ? $popular_books : null);

if ($books_query && $books_query->have_posts()) : ?>
    <div class="pustakabilitas-books-grid">
        <?php while ($books_query->have_posts()) : $books_query->the_post(); ?>
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
                            <i class="fas fa-download"></i>
                            <?php echo number_format_i18n($download_count ? $download_count : 0); ?>
                        </span>
                        <span class="reads">
                            <i class="fas fa-eye"></i>
                            <?php echo number_format_i18n($read_count ? $read_count : 0); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if (isset($show_pagination) && $show_pagination) : ?>
        <div class="pustakabilitas-pagination">
            <?php
            echo paginate_links([
                'total' => $books_query->max_num_pages,
                'current' => get_query_var('paged') ? get_query_var('paged') : 1,
                'prev_text' => '<i class="fas fa-chevron-left"></i>',
                'next_text' => '<i class="fas fa-chevron-right"></i>',
            ]);
            ?>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p class="no-books"><?php _e('No books found.', 'pustakabilitas'); ?></p>
<?php endif; ?> 