document.addEventListener('DOMContentLoaded', function() {
    // Handle book downloads
    const downloadButtons = document.querySelectorAll('.download-button');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const bookId = this.dataset.bookId;
            recordStatistics('download', bookId);
        });
    });

    // Tambahkan ke Koleksi
    $('.add-to-collection').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const bookId = $button.data('book-id');
        
        $.ajax({
            url: pustakabilitasAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_collection',
                book_id: bookId,
                nonce: pustakabilitasAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $button
                        .addClass('added')
                        .html('<i class="dashicons dashicons-yes"></i> Ditambahkan ke Koleksi');
                }
            }
        });
    });
});

function recordStatistics(actionType, bookId) {
    const data = new FormData();
    data.append('action', 'pustakabilitas_record_statistics');
    data.append('book_id', bookId);
    data.append('action_type', actionType);
    data.append('nonce', pustakabilitasAjax.nonce);

    fetch(pustakabilitasAjax.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
    });
}

jQuery(document).ready(function($) {
    $('.add-to-collection').on('click', function(e) {
        e.preventDefault();
        const bookId = $(this).data('book-id');
        
        $.ajax({
            url: pustakabilitasAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_to_collection',
                nonce: pustakabilitasAjax.nonce,
                book_id: bookId
            },
            success: function(response) {
                if(response.success) {
                    alert('Buku berhasil ditambahkan ke koleksi!');
                } else {
                    alert(response.data || 'Gagal menambahkan buku ke koleksi.');
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });
});