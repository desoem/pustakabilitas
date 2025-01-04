<?php
get_header();

wp_enqueue_script('pustakabilitas-book-tracking', plugins_url('assets/js/book-tracking.js', dirname(__FILE__)), array('jquery'), null, true);
wp_localize_script('pustakabilitas-book-tracking', 'pustakabilitasAjax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('pustakabilitas_tracking')
));

while (have_posts()) : the_post();
    // Get book metadata
    $author = get_post_meta(get_the_ID(), '_pustakabilitas_author', true) ?: '-';
    $publisher = get_post_meta(get_the_ID(), '_pustakabilitas_publisher', true) ?: '-';
    $isbn = get_post_meta(get_the_ID(), '_pustakabilitas_isbn', true) ?: '-';
    $file_size = get_post_meta(get_the_ID(), '_pustakabilitas_file_size', true) ?: '-';
    $epub_url = get_post_meta(get_the_ID(), '_pustakabilitas_epub_url', true);
    $audio_url = get_post_meta(get_the_ID(), '_pustakabilitas_audio_url', true);
    $book_type = get_post_meta(get_the_ID(), '_pustakabilitas_book_type', true);
    $book_file = get_post_meta(get_the_ID(), '_pustakabilitas_book_file', true);
    if (empty($book_file)) {
        $book_file = get_post_meta(get_the_ID(), '_pustakabilitas_audio_url', true);
    }

    // Convert audio URL to ncc.html format
    $ncc_url = '';
    if (!empty($book_file)) {
        // Extract the base path from the audio URL 
        $path_parts = explode('/', $book_file);
        array_pop($path_parts); 
        $ncc_url = implode('/', $path_parts) . '/ncc.html';
    }

    // Get plugin URL for dwp
    $plugin_url = plugins_url('', dirname(__FILE__));

    // Construct Daisy player URL
    $player_url = $plugin_url . '/dwp/dwp.html';
    $player_url = add_query_arg([
        'lang' => 'id',
        'ncc' => $ncc_url,
        'book_id' => get_the_ID()
    ], $player_url);

    // Get statistics
    $downloads = count(get_post_meta(get_the_ID(), '_pustakabilitas_downloads', true) ?: []);
    $reads = count(get_post_meta(get_the_ID(), '_pustakabilitas_reads', true) ?: []);
    ?>

    <div class="pustakabilitas-single-book">
        <div class="book-container">
            <!-- Thumbnail Column (1/3) -->
            <div class="book-thumbnail">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('large', ['class' => 'book-cover']); ?>
                <?php else : ?>
                    <div class="no-thumbnail">
                        <i class="dashicons dashicons-book-alt"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details Column (2/3) -->
            <div class="book-details">
                <h1 class="book-title"><?php the_title(); ?></h1>
                
                <div class="book-description">
                    <?php the_content(); ?>
                </div>

                <?php if (is_user_logged_in()) : ?>
                    <div class="book-actions">
                        <?php if (!empty($epub_url)) : ?>
                            <a href="<?php echo esc_url($epub_url); ?>" 
                               class="action-button download-button" 
                               data-book-id="<?php echo get_the_ID(); ?>">
                                <i class="dashicons dashicons-download"></i>
                                <?php _e('Download Buku', 'pustakabilitas'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($ncc_url)) : ?>
                            <a href="<?php echo esc_url($player_url); ?>" 
                               class="action-button button-primary"  
                               target="_blank">
                               <i class="dashicons dashicons-controls-volumeon"></i>
                                <?php _e('Baca Buku', 'pustakabilitas'); ?>
                            </a>
                        <?php endif; ?>

                        <button class="action-button add-to-collection" data-book-id="<?php echo get_the_ID(); ?>">
                            <i class="dashicons dashicons-plus"></i>
                            <?php _e('Tambah ke Koleksi', 'pustakabilitas'); ?>
                        </button>
                    </div>
                <?php else : ?>
                    <div class="login-notice">
                        <p><?php _e('Please login to download or read this book.', 'pustakabilitas'); ?></p>
                        <a href="<?php echo wp_login_url(get_permalink()); ?>" class="login-button">
                            <i class="dashicons dashicons-lock"></i>
                            <?php _e('Login', 'pustakabilitas'); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="book-meta-container">
                    <!-- Book Metadata -->
                    <div class="book-meta">
                        <div class="meta-item">
                            <i class="dashicons dashicons-admin-users"></i>
                            <span class="meta-label"><?php _e('Author:', 'pustakabilitas'); ?></span>
                            <span class="meta-value"><?php echo esc_html($author); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="dashicons dashicons-building"></i>
                            <span class="meta-label"><?php _e('Publisher:', 'pustakabilitas'); ?></span>
                            <span class="meta-value"><?php echo esc_html($publisher); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="dashicons dashicons-book-alt"></i>
                            <span class="meta-label"><?php _e('ISBN:', 'pustakabilitas'); ?></span>
                            <span class="meta-value"><?php echo esc_html($isbn); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="dashicons dashicons-media-default"></i>
                            <span class="meta-label"><?php _e('File Size:', 'pustakabilitas'); ?></span>
                            <span class="meta-value"><?php echo esc_html($file_size); ?></span>
                        </div>
                    </div>

                    <!-- Book Statistics -->
                    <div class="book-stats">
                        <div class="stat-item">
                            <i class="dashicons dashicons-download"></i>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo number_format($downloads); ?></span>
                                <span class="stat-label"><?php _e('Downloads', 'pustakabilitas'); ?></span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="dashicons dashicons-book"></i>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo number_format($reads); ?></span>
                                <span class="stat-label"><?php _e('Reads', 'pustakabilitas'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endwhile;

get_footer(); 