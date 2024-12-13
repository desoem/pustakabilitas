<?php
// Menampilkan statistik buku
$book_id = get_the_ID();
$book_downloads = get_post_meta( $book_id, 'pustakabilitas_downloads', true );
$book_reads = get_post_meta( $book_id, 'pustakabilitas_reads', true );

?>
<div class="book-stats">
    <p><strong>Jumlah Unduhan:</strong> <?php echo $book_downloads; ?></p>
    <p><strong>Jumlah Pembacaan Buku Audio:</strong> <?php echo $book_reads; ?></p>
</div>
