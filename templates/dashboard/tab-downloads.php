<?php
if (!defined('ABSPATH')) exit;

global $pustakabilitas_user_dashboard;
$current_user_id = get_current_user_id();
$downloads = $pustakabilitas_user_dashboard->get_user_downloads($current_user_id);
?>

<div class="downloads-section">
    <header class="section-header">
        <h2>Unduhan Saya</h2>
        <div class="download-controls">
            <div class="search-box">
                <input type="search" class="search-input" placeholder="Cari unduhan..." 
                       aria-label="Cari unduhan">
                <i class="dashicons dashicons-search" aria-hidden="true"></i>
            </div>
            <select class="sort-select" aria-label="Urutkan unduhan">
                <option value="date-desc">Terbaru</option>
                <option value="date-asc">Terlama</option>
                <option value="title-asc">Judul A-Z</option>
                <option value="title-desc">Judul Z-A</option>
            </select>
        </div>
    </header>

    <?php if (!empty($downloads)) : ?>
        <div class="downloads-list">
            <?php foreach ($downloads as $download) : 
                $book = get_post($download['book_id']);
                if (!$book) continue;
                
                $author = get_post_meta($book->ID, 'book_author', true);
                $timestamp = strtotime($download['timestamp']);
                $file_size = $download['file_size'];
                $file_format = isset($download['file_format']) ? $download['file_format'] : 'unknown';
            ?>
                <article class="download-item" data-timestamp="<?php echo esc_attr($timestamp); ?>"
                         data-title="<?php echo esc_attr(get_the_title($book)); ?>">
                    <div class="download-preview">
                        <?php if (has_post_thumbnail($book)) : ?>
                            <?php echo get_the_post_thumbnail($book, 'thumbnail', array('alt' => get_the_title($book))); ?>
                        <?php else : ?>
                            <div class="no-cover">
                                <i class="dashicons dashicons-book-alt" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="download-details">
                        <h3 class="book-title">
                            <a href="<?php echo get_permalink($book); ?>">
                                <?php echo get_the_title($book); ?>
                            </a>
                        </h3>
                        
                        <?php if ($author) : ?>
                            <p class="book-author">
                                <span class="sr-only">Penulis:</span>
                                <?php echo esc_html($author); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="download-meta">
                            <span class="file-info">
                                <i class="dashicons dashicons-media-default" aria-hidden="true"></i>
                                <?php echo strtoupper(esc_html($file_format)); ?> 
                                (<?php echo esc_html(format_file_size($file_size)); ?>)
                            </span>
                            <time datetime="<?php echo esc_attr(date('c', $timestamp)); ?>" class="download-time">
                                <i class="dashicons dashicons-clock" aria-hidden="true"></i>
                                <?php echo human_time_diff($timestamp, current_time('timestamp')); ?> yang lalu
                            </time>
                        </div>
                    </div>
                    
                    <div class="download-actions">
                        <a href="<?php echo esc_url($download['file_url']); ?>" class="action-button primary download-again"
                           download aria-label="Unduh ulang <?php echo esc_attr(get_the_title($book)); ?>">
                            <i class="dashicons dashicons-download" aria-hidden="true"></i>
                            <span class="action-text">Unduh Ulang</span>
                        </a>
                        <button class="action-button remove-download" 
                                data-download-id="<?php echo esc_attr($download['id']); ?>"
                                aria-label="Hapus dari riwayat unduhan">
                            <i class="dashicons dashicons-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="no-downloads-message">
            <i class="dashicons dashicons-download" aria-hidden="true"></i>
            <p>Belum ada riwayat unduhan.</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('pustakabilitas_book')); ?>" class="browse-books-link">
                Jelajahi Buku
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
/* Downloads Section */
.downloads-section {
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.download-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* Search Box */
.search-box {
    position: relative;
}

.search-input {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.875rem;
    width: 200px;
}

.search-box .dashicons {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.sort-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.875rem;
}

/* Downloads List */
.downloads-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.download-item {
    display: flex;
    gap: 1.5rem;
    background: #fff;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.download-item:hover {
    transform: translateY(-2px);
}

.download-preview {
    flex: 0 0 100px;
}

.download-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
}

.no-cover {
    width: 100%;
    height: 150px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
}

.no-cover .dashicons {
    font-size: 2rem;
    width: 2rem;
    height: 2rem;
    color: #666;
}

.download-details {
    flex: 1;
    min-width: 0; /* Prevent text overflow */
}

.book-title {
    margin: 0 0 0.25rem;
    font-size: 1.125rem;
    line-height: 1.4;
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
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
}

.download-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.875rem;
    color: #666;
}

.download-meta .dashicons {
    font-size: 1.125rem;
    width: 1.125rem;
    height: 1.125rem;
    margin-right: 0.25rem;
}

.download-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: center;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.action-button.primary {
    background: var(--wp--preset--color--primary, #0073aa);
    color: #fff;
}

.action-button.primary:hover {
    opacity: 0.9;
}

.action-button:not(.primary) {
    background: #f5f5f5;
    color: #666;
}

.action-button:not(.primary):hover {
    background: #eee;
    color: var(--wp--preset--color--primary, #0073aa);
}

/* No Downloads Message */
.no-downloads-message {
    text-align: center;
    padding: 3rem 1rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.no-downloads-message .dashicons {
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
    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .download-controls {
        width: 100%;
    }
    
    .search-input {
        width: 100%;
    }
    
    .download-item {
        flex-direction: column;
    }
    
    .download-preview {
        flex: 0 0 auto;
        max-width: 150px;
    }
    
    .download-actions {
        flex-direction: row;
        justify-content: flex-end;
        margin-top: 1rem;
    }
}

@media (max-width: 480px) {
    .download-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-button .action-text {
        display: none;
    }
    
    .action-button {
        padding: 0.5rem;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Search functionality
    $('.search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.download-item').each(function() {
            const title = $(this).data('title').toLowerCase();
            $(this).toggle(title.includes(searchTerm));
        });
    });
    
    // Sort functionality
    $('.sort-select').on('change', function() {
        const sortValue = $(this).val();
        const $list = $('.downloads-list');
        const $items = $('.download-item').get();
        
        $items.sort(function(a, b) {
            const $a = $(a);
            const $b = $(b);
            
            switch(sortValue) {
                case 'date-desc':
                    return $b.data('timestamp') - $a.data('timestamp');
                case 'date-asc':
                    return $a.data('timestamp') - $b.data('timestamp');
                case 'title-asc':
                    return $a.data('title').localeCompare($b.data('title'));
                case 'title-desc':
                    return $b.data('title').localeCompare($a.data('title'));
            }
        });
        
        $list.append($items);
    });
    
    // Remove download from history
    $('.remove-download').on('click', function() {
        if (!confirm('Apakah Anda yakin ingin menghapus unduhan ini dari riwayat?')) return;
        
        const $button = $(this);
        const downloadId = $button.data('download-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'remove_download_history',
                download_id: downloadId,
                _ajax_nonce: pustakabilitasAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('.download-item').remove();
                    
                    // Show no downloads message if no items left
                    if ($('.download-item').length === 0) {
                        $('.downloads-list').replaceWith(`
                            <div class="no-downloads-message">
                                <i class="dashicons dashicons-download" aria-hidden="true"></i>
                                <p>Belum ada riwayat unduhan.</p>
                                <a href="${pustakabilitasAjax.archiveUrl}" class="browse-books-link">
                                    Jelajahi Buku
                                </a>
                            </div>
                        `);
                    }
                } else {
                    alert(response.data.message || 'Failed to remove download history');
                }
            },
            error: function() {
                alert('Failed to remove download history');
            }
        });
    });
    
    // Track new downloads
    $('.download-again').on('click', function() {
        const $link = $(this);
        const downloadId = $link.closest('.download-item').find('.remove-download').data('download-id');
        
        // Send AJAX request to track download
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'track_book_download',
                download_id: downloadId,
                _ajax_nonce: pustakabilitasAjax.nonce
            }
        });
    });
});
</script>
