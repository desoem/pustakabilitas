jQuery(document).ready(function($) {
    // Tab functionality
    const $tabButtons = $('.tab-button');
    const $tabPanels = $('.tab-panel');
    
    $tabButtons.on('click', function() {
        const $button = $(this);
        const panelId = $button.attr('aria-controls');
        
        // Update button states
        $tabButtons.removeClass('active').attr('aria-selected', 'false');
        $button.addClass('active').attr('aria-selected', 'true');
        
        // Update panel states
        $tabPanels.removeClass('active').attr('aria-hidden', 'true');
        $(`#${panelId}`).addClass('active').attr('aria-hidden', 'false');
    });

    // Search functionality
    $('.search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const $items = $(this).closest('.tab-panel').find('.item');
        
        $items.each(function() {
            const $item = $(this);
            const title = $item.find('.item-title').text().toLowerCase();
            $item.toggle(title.includes(searchTerm));
        });
    });

    // Sort functionality
    $('.sort-select').on('change', function() {
        const $select = $(this);
        const sortValue = $select.val();
        const $container = $select.closest('.tab-panel').find('.items-grid');
        const $items = $container.children('.item').get();
        
        $items.sort(function(a, b) {
            const $a = $(a);
            const $b = $(b);
            
            switch(sortValue) {
                case 'date-desc':
                    return new Date($b.data('date')) - new Date($a.data('date'));
                case 'date-asc':
                    return new Date($a.data('date')) - new Date($b.data('date'));
                case 'title-asc':
                    return $a.find('.item-title').text().localeCompare($b.find('.item-title').text());
                case 'title-desc':
                    return $b.find('.item-title').text().localeCompare($a.find('.item-title').text());
            }
        });
        
        $container.append($items);
    });

    // AJAX handlers
    function handleAjaxError(error) {
        console.error('AJAX Error:', error);
        alert(pustakabilitasAjax.strings.errorLoading);
    }

    // Bookmark actions
    $('.toggle-bookmark').on('click', function(e) {
        e.preventDefault();
        const $button = $(this);
        const bookId = $button.data('book-id');
        
        $.ajax({
            url: pustakabilitasAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_bookmark',
                nonce: pustakabilitasAjax.nonce,
                book_id: bookId
            },
            success: function(response) {
                if (response.success) {
                    $button.toggleClass('bookmarked');
                    // Update UI as needed
                }
            },
            error: handleAjaxError
        });
    });

    // Note actions
    let $activeNoteButton = null;
    
    $('.edit-note').on('click', function() {
        $activeNoteButton = $(this);
        const note = $activeNoteButton.data('note') || '';
        $('#bookmark-note').val(note);
        $('.edit-note-modal').addClass('active');
    });

    $('.close-modal').on('click', function() {
        $('.edit-note-modal').removeClass('active');
    });

    $('#save-note').on('click', function() {
        if (!$activeNoteButton) return;
        
        const bookId = $activeNoteButton.data('book-id');
        const note = $('#bookmark-note').val();
        
        $.ajax({
            url: pustakabilitasAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_bookmark_note',
                nonce: pustakabilitasAjax.nonce,
                book_id: bookId,
                note: note
            },
            success: function(response) {
                if (response.success) {
                    $activeNoteButton.data('note', note);
                    $('.edit-note-modal').removeClass('active');
                }
            },
            error: handleAjaxError
        });
    });
});
