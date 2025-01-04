<?php
if (!defined('ABSPATH')) exit;

global $pustakabilitas_user_dashboard;
$current_user_id = get_current_user_id();
$reading_history = $pustakabilitas_user_dashboard->get_user_reading_history(20); // Get last 20 entries
?>

<div class="reading-history-section">
    <header class="section-header">
        <h2>Riwayat Baca</h2>
        <div class="history-filters">
            <select class="time-filter" aria-label="Filter waktu">
                <option value="all">Semua Waktu</option>
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
            </select>
        </div>
    </header>

    <?php if (!empty($reading_history)) : ?>
        <div class="history-timeline">
            <?php foreach ($reading_history as $entry) : 
                $book = get_post($entry['book_id']);
                if (!$book) continue;
                
                $progress = $entry['progress'];
                $timestamp = strtotime($entry['timestamp']);
                $date = wp_date('j F Y', $timestamp);
                $time = wp_date('H:i', $timestamp);
                $last_page = isset($entry['last_page']) ? $entry['last_page'] : 0;
            ?>
                <article class="history-item" data-timestamp="<?php echo esc_attr($timestamp); ?>">
                    <div class="history-meta">
                        <time datetime="<?php echo esc_attr(date('c', $timestamp)); ?>" class="history-time">
                            <?php echo esc_html($date); ?> <span class="time"><?php echo esc_html($time); ?></span>
                        </time>
                    </div>
                    
                    <div class="history-content">
                        <div class="book-preview">
                            <?php if (has_post_thumbnail($book)) : ?>
                                <?php echo get_the_post_thumbnail($book, 'thumbnail', array('alt' => get_the_title($book))); ?>
                            <?php else : ?>
                                <div class="no-cover">
                                    <i class="dashicons dashicons-book-alt" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="history-details">
                            <h3 class="book-title">
                                <a href="<?php echo get_permalink($book); ?>">
                                    <?php echo get_the_title($book); ?>
                                </a>
                            </h3>
                            
                            <?php 
                            $author = get_post_meta($book->ID, 'book_author', true);
                            if ($author) : ?>
                                <p class="book-author">
                                    <span class="sr-only">Penulis:</span>
                                    <?php echo esc_html($author); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="reading-progress" role="progressbar" 
                                 aria-valuenow="<?php echo esc_attr($progress); ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: <?php echo esc_attr($progress); ?>%">
                                    <span class="progress-text"><?php echo esc_html($progress); ?>% selesai</span>
                                </div>
                            </div>
                            
                            <div class="history-actions">
                                <a href="<?php echo get_permalink($book); ?>" class="action-button primary">
                                    Lanjutkan Membaca
                                </a>
                                <?php if ($last_page) : ?>
                                    <span class="last-page">
                                        Halaman terakhir: <?php echo esc_html($last_page); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            
            <?php if (count($reading_history) >= 20) : ?>
                <div class="load-more-container">
                    <button class="load-more-button" data-page="2">
                        Muat Lebih Banyak
                        <i class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <div class="no-history-message">
            <i class="dashicons dashicons-clock" aria-hidden="true"></i>
            <p>Belum ada riwayat baca.</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('pustakabilitas_book')); ?>" class="browse-books-link">
                Mulai Membaca
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
/* Reading History Section */
.reading-history-section {
    max-width: 800px;
    margin: 0 auto;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.history-filters {
    display: flex;
    gap: 1rem;
}

.time-filter {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    font-size: 0.875rem;
}

/* Timeline */
.history-timeline {
    position: relative;
    padding-left: 2rem;
}

.history-timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #eee;
}

.history-item {
    position: relative;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.history-item:last-child {
    border-bottom: none;
}

.history-item::before {
    content: '';
    position: absolute;
    left: -2rem;
    top: 1.5rem;
    width: 1rem;
    height: 1rem;
    background: var(--wp--preset--color--primary, #0073aa);
    border: 2px solid #fff;
    border-radius: 50%;
    transform: translateX(50%);
}

.history-meta {
    margin-bottom: 1rem;
}

.history-time {
    font-size: 0.875rem;
    color: #666;
}

.history-time .time {
    opacity: 0.7;
}

.history-content {
    display: flex;
    gap: 1.5rem;
    background: #fff;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.book-preview {
    flex: 0 0 100px;
}

.book-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
}

.history-details {
    flex: 1;
}

.book-title {
    margin: 0 0 0.5rem;
    font-size: 1.125rem;
}

.book-title a {
    color: inherit;
    text-decoration: none;
}

.book-title a:hover {
    color: var(--wp--preset--color--primary, #0073aa);
}

.book-author {
    color: #666;
    margin: 0 0 1rem;
    font-size: 0.875rem;
}

.reading-progress {
    height: 4px;
    background: #eee;
    border-radius: 2px;
    margin: 1rem 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--wp--preset--color--primary, #0073aa);
    transition: width 0.3s ease;
}

.progress-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #666;
}

.history-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.action-button {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.3s ease;
    width: auto;
}

.action-button.primary {
    background: var(--wp--preset--color--primary, #0073aa);
    color: #fff;
    white-space: nowrap;
}

.action-button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.last-page {
    font-size: 0.875rem;
    color: #666;
}

/* Load More */
.load-more-container {
    text-align: center;
    margin-top: 2rem;
}

.load-more-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #f5f5f5;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.load-more-button:hover {
    background: #eee;
}

.load-more-button .dashicons {
    transition: transform 0.3s ease;
}

.load-more-button:hover .dashicons {
    transform: translateY(2px);
}

/* No History Message */
.no-history-message {
    text-align: center;
    padding: 3rem 1rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.no-history-message .dashicons {
    font-size: 3rem;
    width: 3rem;
    height: 3rem;
    color: #666;
    margin-bottom: 1rem;
}

.browse-books-link {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background: var(--wp--preset--color--primary, #0073aa);
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.browse-books-link:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .history-content {
        flex-direction: column;
    }
    
    .book-preview {
        flex: 0 0 auto;
        max-width: 150px;
    }
}

@media (max-width: 480px) {
    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .history-timeline {
        padding-left: 1.5rem;
    }
    
    .history-item::before {
        left: -1.5rem;
    }
    
    .history-actions {
        gap: 0.5rem;
    }
    
    .action-button {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Time filter
    $('.time-filter').on('change', function() {
        const filter = $(this).val();
        const now = new Date().getTime() / 1000;
        const day = 24 * 60 * 60;
        
        $('.history-item').each(function() {
            const timestamp = parseInt($(this).data('timestamp'));
            const diff = now - timestamp;
            
            switch(filter) {
                case 'today':
                    $(this).toggle(diff < day);
                    break;
                case 'week':
                    $(this).toggle(diff < day * 7);
                    break;
                case 'month':
                    $(this).toggle(diff < day * 30);
                    break;
                default:
                    $(this).show();
            }
        });
    });
    
    // Load more
    $('.load-more-button').on('click', function() {
        const $button = $(this);
        const page = $button.data('page');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'load_more_reading_history',
                page: page,
                _ajax_nonce: pustakabilitasAjax.nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Append new items
                    $('.history-timeline').append(response.data.html);
                    
                    // Update page number
                    $button.data('page', page + 1);
                    
                    // Hide button if no more pages
                    if (!response.data.has_more) {
                        $button.parent().remove();
                    }
                } else {
                    alert(response.data.message || 'Failed to load more items');
                }
            },
            error: function() {
                alert('Failed to load more items');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});
</script>
