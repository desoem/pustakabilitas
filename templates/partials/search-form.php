<?php
/**
 * Template part for displaying search form
 * Mengikuti standar WCAG 2.2 untuk aksesibilitas
 */
?>
<form role="search" method="get" class="pustakabilitas-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="search-wrapper" role="search">
        <label for="book-search" class="screen-reader-text">
            <?php echo esc_html_x('Search for books', 'label', 'pustakabilitas'); ?>
        </label>
        
        <div class="search-input-wrapper">
            <input type="search" 
                   id="book-search"
                   class="search-field" 
                   placeholder="<?php echo esc_attr_x('Search for books...', 'placeholder', 'pustakabilitas'); ?>"
                   value="<?php echo get_search_query(); ?>" 
                   name="s"
                   aria-label="<?php echo esc_attr_x('Search books', 'aria-label', 'pustakabilitas'); ?>"
                   autocomplete="off"
                   required
            >
            <button type="submit" class="search-submit">
                <span class="search-icon">
                    <i class="eicon-search" aria-hidden="true"></i>
                </span>
            </button>
        </div>

        <input type="hidden" name="post_type" value="pustakabilitas_book">
    </div>
</form> 