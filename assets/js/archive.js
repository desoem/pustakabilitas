jQuery(document).ready(function($) {
    // Category filter
    $('.categories-nav button').on('click', function() {
        const $button = $(this);
        const category = $button.data('category');
        
        // Update active state
        $('.categories-nav button').removeClass('active');
        $button.addClass('active');
        
        // Filter books
        if (category === 'all') {
            $('.book-card').show();
        } else {
            $('.book-card').each(function() {
                const $card = $(this);
                const hasCategory = $card.find('.book-category').toArray()
                    .some(el => $(el).text().trim().toLowerCase() === category.toLowerCase());
                
                $card.toggle(hasCategory);
            });
        }
    });

    // Live search
    let searchTimer;
    $('.search-field').on('input', function() {
        clearTimeout(searchTimer);
        
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimer = setTimeout(function() {
            $('.book-card').each(function() {
                const $card = $(this);
                const title = $card.find('.book-title').text().toLowerCase();
                const author = $card.find('.book-author').text().toLowerCase();
                const categories = $card.find('.book-category').text().toLowerCase();
                
                const matches = title.includes(searchTerm) || 
                              author.includes(searchTerm) || 
                              categories.includes(searchTerm);
                
                $card.toggle(matches);
            });
        }, 300);
    });
});
