<?php
get_header();

while (have_posts()) : the_post();
    // Get book metadata
    $author = get_post_meta(get_the_ID(), '_pustakabilitas_author', true) ?: '-';
    $publisher = get_post_meta(get_the_ID(), '_pustakabilitas_publisher', true) ?: '-';
    $isbn = get_post_meta(get_the_ID(), '_pustakabilitas_isbn', true) ?: '-';
    $file_size = get_post_meta(get_the_ID(), '_pustakabilitas_file_size', true) ?: '-';
    $epub_url = get_post_meta(get_the_ID(), '_pustakabilitas_epub_url', true);
    $audio_url = get_post_meta(get_the_ID(), '_pustakabilitas_audio_url', true);
    $book_type = get_post_meta(get_the_ID(), '_pustakabilitas_book_type', true);
    
    // Get statistics
    $downloads = count(get_post_meta(get_the_ID(), '_pustakabilitas_downloads', true) ?: []);
    $reads = count(get_post_meta(get_the_ID(), '_pustakabilitas_reads', true) ?: []);
    ?>

    <style>
        .pustakabilitas-single-book {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }
        
        .book-container {
            display: grid;
            grid-template-columns: 1fr 2fr;  /* 1/3 : 2/3 ratio */
            gap: 30px;
        }
        
        /* Left Column - Thumbnail */
        .book-thumbnail {
            width: 100%;
        }
        
        .book-thumbnail img.book-cover {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Right Column - Content */
        .book-details {
            width: 100%;
        }
        
        .book-title {
            margin-bottom: 20px;
            font-size: 2em;
            color: #333;
        }
        
        .book-description {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 30px;
            padding: 15px 0;
        }
        
        .book-actions {
            display: flex;
            gap: 15px;
            padding: 15px 0;
        }
        
        .action-button {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .download-button {
            background-color: #4CAF50;
            color: white;
        }
        
        .audio-button {
            background-color: #2196F3;
            color: white;
        }
        
        .action-button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .action-button i {
            margin-right: 8px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .book-container {
                grid-template-columns: 1fr;
            }
            
            .book-thumbnail {
                max-width: 400px;
                margin: 0 auto;
            }
            
            .book-actions {
                flex-direction: column;
            }
            
            .action-button {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Book Metadata Styling */
        .book-meta-container {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .book-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
        }

        .meta-item i {
            margin-right: 10px;
            color: #666;
            font-size: 20px;
        }

        .meta-label {
            font-weight: 600;
            margin-right: 8px;
            color: #333;
        }

        .meta-value {
            color: #666;
        }

        /* Statistics Styling */
        .book-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-top: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-count {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
        }

        .stat-label {
            color: #666;
        }

        @media (max-width: 768px) {
            .book-meta {
                grid-template-columns: 1fr;
            }
            
            .book-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>

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
                        <?php 
                        // Tampilkan tombol Download jika ada EPUB URL
                        if (!empty($epub_url)) : ?>
                            <a href="<?php echo esc_url($epub_url); ?>" 
                               class="action-button download-button" 
                               data-book-id="<?php echo get_the_ID(); ?>">
                                <i class="dashicons dashicons-download"></i>
                                <?php _e('Download Buku ePub', 'pustakabilitas'); ?>
                            </a>
                        <?php endif; ?>

                        <?php 
                        // Tampilkan tombol Audio jika ada Audio URL
                        if (!empty($audio_url)) : ?>
                            <a href="#" 
                               class="action-button audio-button" 
                               data-book-id="<?php echo get_the_ID(); ?>" 
                               data-audio-url="<?php echo esc_url($audio_url); ?>"
                               onclick="openDaisyPlayer(event, this);">
                                <i class="dashicons dashicons-controls-volumeon"></i>
                                <?php _e('Baca Buku Audio', 'pustakabilitas'); ?>
                            </a>
                        <?php endif; ?>
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