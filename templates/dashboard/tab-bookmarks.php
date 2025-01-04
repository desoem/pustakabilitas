<?php
if (!defined('ABSPATH')) exit;

global $pustakabilitas_user_dashboard;
$current_user_id = get_current_user_id();
$bookmarks = $pustakabilitas_user_dashboard->get_user_bookmarks($current_user_id);
?>

<div class="bookmarks-section">
    <header class="section-header">
        <h2>Bookmark Saya</h2>
        <div class="bookmark-controls">
            <div class="search-box">
                <input type="search" class="search-input" placeholder="Cari bookmark..." 
                       aria-label="Cari bookmark">
                <i class="dashicons dashicons-search" aria-hidden="true"></i>
            </div>
            <select class="sort-select" aria-label="Urutkan bookmark">
                <option value="date-desc">Terbaru</option>
                <option value="date-asc">Terlama</option>
                <option value="title-asc">Judul A-Z</option>
                <option value="title-desc">Judul Z-A</option>
            </select>
        </div>
    </header>

    <?php if (!empty($bookmarks)) : ?>
        <div class="bookmarks-grid">
            <?php foreach ($bookmarks as $bookmark) : 
                $book = get_post($bookmark['book_id']);
                if (!$book) continue;
                
                $author = get_post_meta($book->ID, 'book_author', true);
                $timestamp = strtotime($bookmark['timestamp']);
                $page = $bookmark['page'];
                $note = $bookmark['note'];
            ?>
                <article class="bookmark-card" data-timestamp="<?php echo esc_attr($timestamp); ?>"
                         data-title="<?php echo esc_attr(get_the_title($book)); ?>">
                    <div class="bookmark-header">
                        <div class="book-preview">
                            <?php if (has_post_thumbnail($book)) : ?>
                                <?php echo get_the_post_thumbnail($book, 'thumbnail', array('alt' => get_the_title($book))); ?>
                            <?php else : ?>
                                <div class="no-cover">
                                    <i class="dashicons dashicons-book-alt" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bookmark-meta">
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
                            
                            <div class="bookmark-info">
                                <span class="page-number">
                                    <i class="dashicons dashicons-book" aria-hidden="true"></i>
                                    Halaman <?php echo esc_html($page); ?>
                                </span>
                                <time datetime="<?php echo esc_attr(date('c', $timestamp)); ?>" class="bookmark-time">
                                    <i class="dashicons dashicons-clock" aria-hidden="true"></i>
                                    <?php echo human_time_diff($timestamp, current_time('timestamp')); ?> yang lalu
                                </time>
                            </div>
                        </div>
                        
                        <div class="bookmark-actions">
                            <button class="action-button edit-note" aria-label="Edit catatan"
                                    data-bookmark-id="<?php echo esc_attr($bookmark['id']); ?>">
                                <i class="dashicons dashicons-edit" aria-hidden="true"></i>
                            </button>
                            <button class="action-button remove-bookmark" aria-label="Hapus bookmark"
                                    data-bookmark-id="<?php echo esc_attr($bookmark['id']); ?>">
                                <i class="dashicons dashicons-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($note) : ?>
                        <div class="bookmark-note">
                            <p><?php echo esc_html($note); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bookmark-footer">
                        <a href="<?php echo add_query_arg('page', $page, get_permalink($book)); ?>" 
                           class="action-button primary">
                            Buka Halaman
                            <i class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="no-bookmarks-message">
            <i class="dashicons dashicons-bookmark" aria-hidden="true"></i>
            <p>Belum ada bookmark.</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('pustakabilitas_book')); ?>" class="browse-books-link">
                Jelajahi Buku
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Note Modal -->
<div class="modal edit-note-modal" role="dialog" aria-labelledby="edit-note-title" aria-hidden="true">
    <div class="modal-content">
        <header class="modal-header">
            <h3 id="edit-note-title">Edit Catatan</h3>
            <button class="close-modal" aria-label="Tutup">
                <i class="dashicons dashicons-no-alt" aria-hidden="true"></i>
            </button>
        </header>
        
        <div class="modal-body">
            <form id="edit-note-form">
                <input type="hidden" name="bookmark_id" id="edit-bookmark-id">
                <div class="form-group">
                    <label for="bookmark-note">Catatan:</label>
                    <textarea id="bookmark-note" name="note" rows="4" 
                              placeholder="Tambahkan catatan untuk bookmark ini..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="action-button primary">Simpan</button>
                    <button type="button" class="action-button cancel-edit">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Bookmarks Section */
.bookmarks-section {
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.bookmark-controls {
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

/* Bookmarks Grid */
.bookmarks-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.bookmark-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.bookmark-header {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.book-preview {
    flex: 0 0 80px;
}

.book-preview img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 4px;
}

.bookmark-meta {
    flex: 1;
}

.book-title {
    margin: 0 0 0.25rem;
    font-size: 1rem;
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

.bookmark-info {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.75rem;
    color: #666;
}

.bookmark-info .dashicons {
    font-size: 1rem;
    width: 1rem;
    height: 1rem;
    margin-right: 0.25rem;
}

.bookmark-actions {
    display: flex;
    gap: 0.5rem;
}

.action-button {
    padding: 0.5rem;
    border: none;
    background: none;
    color: #666;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.action-button:hover {
    background: #f5f5f5;
    color: var(--wp--preset--color--primary, #0073aa);
}

.bookmark-note {
    padding: 1rem;
    background: #f9f9f9;
    font-size: 0.875rem;
    color: #666;
}

.bookmark-footer {
    padding: 1rem;
    display: flex;
    justify-content: flex-end;
}

.action-button.primary {
    background: var(--wp--preset--color--primary, #0073aa);
    color: #fff;
    padding: 0.5rem 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.action-button.primary:hover {
    opacity: 0.9;
    color: #fff;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    padding: 1rem;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #fff;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.close-modal {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
}

.close-modal:hover {
    background: #f5f5f5;
}

.modal-body {
    padding: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

/* No Bookmarks Message */
.no-bookmarks-message {
    text-align: center;
    padding: 3rem 1rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.no-bookmarks-message .dashicons {
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
    
    .bookmark-controls {
        width: 100%;
    }
    
    .search-input {
        width: 100%;
    }
    
    .bookmarks-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .bookmark-header {
        flex-direction: column;
    }
    
    .book-preview {
        flex: 0 0 auto;
        max-width: 120px;
    }
    
    .bookmark-actions {
        justify-content: flex-end;
        margin-top: 1rem;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Search functionality
    $('.search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.bookmark-card').each(function() {
            const title = $(this).data('title').toLowerCase();
            $(this).toggle(title.includes(searchTerm));
        });
    });
    
    // Sort functionality
    $('.sort-select').on('change', function() {
        const sortValue = $(this).val();
        const $grid = $('.bookmarks-grid');
        const $cards = $('.bookmark-card').get();
        
        $cards.sort(function(a, b) {
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
        
        $grid.append($cards);
    });
    
    // Modal functionality
    function openModal() {
        $('.edit-note-modal').addClass('active');
    }
    
    function closeModal() {
        $('.edit-note-modal').removeClass('active');
    }
    
    $('.edit-note').on('click', function() {
        const bookmarkId = $(this).data('bookmark-id');
        const $card = $(this).closest('.bookmark-card');
        const note = $card.find('.bookmark-note p').text();
        
        $('#edit-bookmark-id').val(bookmarkId);
        $('#bookmark-note').val(note);
        
        openModal();
    });
    
    $('.close-modal, .cancel-edit').on('click', closeModal);
    
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
    
    // Edit note form submission
    $('#edit-note-form').on('submit', function(e) {
        e.preventDefault();
        
        const bookmarkId = $('#edit-bookmark-id').val();
        const note = $('#bookmark-note').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_bookmark_note',
                bookmark_id: bookmarkId,
                note: note,
                _ajax_nonce: pustakabilitasAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const $card = $(`.bookmark-card:has([data-bookmark-id="${bookmarkId}"])`);
                    const $note = $card.find('.bookmark-note');
                    
                    if (note) {
                        if ($note.length) {
                            $note.find('p').text(note);
                        } else {
                            $card.find('.bookmark-header').after(`
                                <div class="bookmark-note">
                                    <p>${note}</p>
                                </div>
                            `);
                        }
                    } else {
                        $note.remove();
                    }
                    
                    closeModal();
                } else {
                    alert(response.data.message || 'Failed to update note');
                }
            },
            error: function() {
                alert('Failed to update note');
            }
        });
    });
    
    // Remove bookmark
    $('.remove-bookmark').on('click', function() {
        if (!confirm('Apakah Anda yakin ingin menghapus bookmark ini?')) return;
        
        const $button = $(this);
        const bookmarkId = $button.data('bookmark-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'remove_bookmark',
                bookmark_id: bookmarkId,
                _ajax_nonce: pustakabilitasAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $button.closest('.bookmark-card').remove();
                    
                    // Show no bookmarks message if no cards left
                    if ($('.bookmark-card').length === 0) {
                        $('.bookmarks-grid').replaceWith(`
                            <div class="no-bookmarks-message">
                                <i class="dashicons dashicons-bookmark" aria-hidden="true"></i>
                                <p>Belum ada bookmark.</p>
                                <a href="${pustakabilitasAjax.archiveUrl}" class="browse-books-link">
                                    Jelajahi Buku
                                </a>
                            </div>
                        `);
                    }
                } else {
                    alert(response.data.message || 'Failed to remove bookmark');
                }
            },
            error: function() {
                alert('Failed to remove bookmark');
            }
        });
    });
});
</script>
