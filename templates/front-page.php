<?php
/**
 * Template Name: Pustakabilitas Front Page
 */

get_header();
?>

<div class="pustakabilitas-front-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1><?php _e('Welcome to Digital Library', 'pustakabilitas'); ?></h1>
            <p class="tagline"><?php echo get_theme_mod('pustakabilitas_tagline', __('Access to knowledge for everyone', 'pustakabilitas')); ?></p>
            
            <!-- Search Form -->
            <div class="search-container">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" 
                           class="search-field" 
                           placeholder="<?php _e('Search books by title, author, or publisher...', 'pustakabilitas'); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s" />
                    <input type="hidden" name="post_type" value="pustakabilitas_book" />
                    <button type="submit" class="search-submit">
                        <?php _e('Search', 'pustakabilitas'); ?>
                    </button>
                </form>
            </div>

            <!-- Statistics -->
            <div class="global-stats">
                <?php
                $total_books = wp_count_posts('pustakabilitas_book')->publish;
                $total_downloads = $this->get_total_downloads();
                $total_reads = $this->get_total_reads();
                $total_users = count_users()['total_users'];
                ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($total_books); ?></span>
                    <span class="stat-label"><?php _e('Total Books', 'pustakabilitas'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($total_downloads); ?></span>
                    <span class="stat-label"><?php _e('Downloads', 'pustakabilitas'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($total_reads); ?></span>
                    <span class="stat-label"><?php _e('Books Read', 'pustakabilitas'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($total_users); ?></span>
                    <span class="stat-label"><?php _e('Users', 'pustakabilitas'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <h2><?php _e('Reading Books, Reading the World...', 'pustakabilitas'); ?></h2>
            <div class="about-content">
                <?php echo wpautop(get_theme_mod('pustakabilitas_about_text', '')); ?>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?> 