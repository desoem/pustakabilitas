jQuery(document).ready(function($) {
    // Track downloads
    $('.download-button').on('click', function(e) {
        var bookId = $(this).data('book-id');
        
        $.ajax({
            url: pustakabilitasAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'track_book_action',
                nonce: pustakabilitasAjax.nonce,
                book_id: bookId,
                action_type: 'download'
            }
        });
    });

    // Track audio reads
    $('.button-primary').on('click', function(e) {
        var bookId = $(this).closest('.book-actions').find('.download-button').data('book-id');
        
        $.ajax({
            url: pustakabilitasAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'track_book_action',
                nonce: pustakabilitasAjax.nonce,
                book_id: bookId,
                action_type: 'read'
            }
        });
    });
}); 