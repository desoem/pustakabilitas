<?php
// Menampilkan tombol Download Buku ePub dan Baca Buku Audio
if ( is_user_logged_in() ) :
    ?>
    <div class="book-actions">
        <a href="<?php echo esc_url( get_post_meta( get_the_ID(), 'epub_url', true ) ); ?>" class="button download-epub" data-book-id="<?php the_ID(); ?>">Download Buku ePub</a>
        <a href="#" id="daisy-player-link" data-book-id="<?php the_ID(); ?>" class="button baca-buku-audio">Baca Buku Audio</a>
    </div>
    <?php
else :
    echo '<p>Silakan login untuk mengunduh atau membaca buku.</p>';
endif;
