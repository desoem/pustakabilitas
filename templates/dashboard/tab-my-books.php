<?php
if (!defined('ABSPATH')) exit;

$current_user_id = get_current_user_id();
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Query untuk mengambil buku yang pernah diunduh atau dibaca
$args = array(
    'post_type' => 'pustakabilitas_book',
    'posts_per_page' => 12,
    'paged' => $paged,
    'meta_query' => array(
        'relation' => 'OR',
        // Buku yang pernah diunduh
        array(
            'key' => '_pustakabilitas_downloads',
            'value' => sprintf(':%d;', $current_user_id),
            'compare' => 'LIKE'
        ),
        // Buku yang pernah dibaca
        array(
            'key' => '_pustakabilitas_reads',
            'value' => sprintf(':%d;', $current_user_id),
            'compare' => 'LIKE'
        )
    )
);

$books_query = new WP_Query($args);
?>

<div class="my-books-section">
    <h2>Koleksi Buku Saya</h2>

    <?php if ($books_query->have_posts()) : ?>
        <div class="books-container">
            <?php while ($books_query->have_posts()) : $books_query->the_post(); 
                $epub_url = get_post_meta(get_the_ID(), '_pustakabilitas_epub_url', true);
                $book_file = get_post_meta(get_the_ID(), '_pustakabilitas_book_file', true);
                if (empty($book_file)) {
                    $book_file = get_post_meta(get_the_ID(), '_pustakabilitas_audio_url', true);
                }
                
                // Membuat URL untuk player audio
                $ncc_url = '';
                if (!empty($book_file)) {
                    $path_parts = explode('/', $book_file);
                    array_pop($path_parts);
                    $ncc_url = implode('/', $path_parts) . '/ncc.html';
                }
                $player_url = plugins_url('', dirname(dirname(__FILE__))) . '/dwp/dwp.html';
                $player_url = add_query_arg([
                    'lang' => 'id',
                    'ncc' => $ncc_url,
                    'book_id' => get_the_ID()
                ], $player_url);
            ?>
                <article class="book-item">
                    <div class="book-cover">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium'); ?>
                        <?php endif; ?>
                    </div>

                    <div class="book-details">
                        <h3><?php the_title(); ?></h3>
                        
                        <div class="book-actions">
                            <?php if (!empty($epub_url)) : ?>
                                <a href="<?php echo esc_url($epub_url); ?>" 
                                   class="action-button download-button" 
                                   data-book-id="<?php echo get_the_ID(); ?>">
                                    <i class="dashicons dashicons-download"></i>
                                    Download Buku Epub
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($ncc_url)) : ?>
                                <a href="<?php echo esc_url($player_url); ?>" 
                                   class="action-button read-button" 
                                   target="_blank">
                                    <i class="dashicons dashicons-controls-volumeon"></i>
                                    Baca Buku Audio
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php if ($books_query->max_num_pages > 1) : ?>
            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'total' => $books_query->max_num_pages,
                    'current' => $paged
                ));
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="no-books-message">
            <p>Anda belum memiliki buku dalam koleksi.</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('pustakabilitas_book')); ?>" class="browse-books-link">
                Jelajahi Katalog Buku
            </a>
        </div>
    <?php endif; 
    wp_reset_postdata();
    ?>
</div>

<style>
.my-books-section {
    padding: 0;
}

.books-container {
    display: grid;
    gap: 8px;
    margin: 0.5rem -15px;
    grid-template-columns: repeat(2, 1fr);
}

.book-item {
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.book-cover img {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 3/4;
    object-fit: cover;
}

.book-details {
    padding: 0.75rem;
}

.book-details h3 {
    margin: 0 0 0.75rem;
    font-size: 1rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.book-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    border-radius: 4px;
    text-decoration: none;
    color: #fff;
    background: #0073aa;
    transition: opacity 0.2s;
    font-size: 0.8rem;
    width: 100%;
    justify-content: center;
}

.action-button:hover {
    opacity: 0.9;
    color: #fff;
}

.no-books-message {
    text-align: center;
    padding: 2rem;
    background: #f5f5f5;
    border-radius: 8px;
}

.browse-books-link {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background: #0073aa;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
}

/* Tablet (768px dan ke atas) */
@media (min-width: 768px) {
    .my-books-section {
        padding: 0;
    }

    .books-container {
        margin: 0.5rem 0;
        gap: 15px;
        grid-template-columns: repeat(3, 1fr);
    }

    .book-details {
        padding: 1rem;
    }

    .book-details h3 {
        font-size: 1.1rem;
    }

    .action-button {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}

/* Desktop (1024px dan ke atas) */
@media (min-width: 1024px) {
    .my-books-section {
        padding: 0;
    }

    .books-container {
        gap: 20px;
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Penyesuaian untuk layar sangat kecil */
@media (max-width: 480px) {
    .my-books-section {
        padding: 0;
    }

    .books-container {
        margin: 0.5rem -15px;
        gap: 8px;
    }

    .book-details {
        padding: 0.5rem;
    }

    .book-details h3 {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
}
</style>
