<?php
/**
 * Template for displaying book archive
 */

get_header();
?>

<div class="pustakabilitas-archive">
    <div class="container">
        <h1 class="page-title"><?php _e('Digital Library', 'pustakabilitas'); ?></h1>

        <!-- Search Form -->
        <div class="archive-search">
            <form role="search" method="get" class="search-form">
                <input type="search" 
                       class="search-field" 
                       placeholder="<?php _e('Search books...', 'pustakabilitas'); ?>" 
                       value="<?php echo get_search_query(); ?>" 
                       name="s" />
                <input type="hidden" name="post_type" value="pustakabilitas_book" />
                
                <!-- Category Filter -->
                <?php
                $categories = get_categories([
                    'taxonomy' => 'category',
                    'hide_empty' => true,
                ]);
                if ($categories) : ?>
                    <select name="book_category" class="category-filter">
                        <option value=""><?php _e('All Categories', 'pustakabilitas'); ?></option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>"
                                    <?php selected(get_query_var('book_category'), $category->term_id); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                
                <button type="submit" class="search-submit">
                    <?php _e('Search', 'pustakabilitas'); ?>
                </button>
            </form>
        </div>

        <!-- Category List -->
        <div class="category-list">
            <?php
            foreach ($categories as $category) {
                printf(
                    '<a href="%s" class="category-tag">%s</a>',
                    esc_url(get_category_link($category->term_id)),
                    esc_html($category->name)
                );
            }
            ?>
        </div>

        <!-- Books Grid -->
        <div class="books-grid">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
                    get_template_part('templates/partials/book', 'card');
                endwhile;
            else :
                echo '<p class="no-books">' . __('No books found.', 'pustakabilitas') . '</p>';
            endif;
            ?>
        </div>

        <!-- Pagination -->
        <div class="pagination" data-ajax="true">
            <?php
            echo paginate_links([
                'prev_text' => __('Previous', 'pustakabilitas'),
                'next_text' => __('Next', 'pustakabilitas'),
            ]);
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
