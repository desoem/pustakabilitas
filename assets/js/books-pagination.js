jQuery(document).ready(function($) {
    $('.pustakabilitas-books-container').each(function() {
        const container = $(this);
        const settings = container.data('settings');
        
        container.on('click', '.pustakabilitas-pagination a', function(e) {
            e.preventDefault();
            
            const page = $(this).data('page');
            const loading = container.find('.pustakabilitas-loading');
            
            // Update active class
            container.find('.page-numbers').removeClass('current');
            $(this).addClass('current');
            
            // Show loading
            loading.show();
            container.find('.pustakabilitas-books-grid').addClass('loading');
            
            $.ajax({
                url: pustakabilitasAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_more_books',
                    page: page,
                    settings: settings,
                    nonce: pustakabilitasAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        container.find('.pustakabilitas-books-grid').html(response.data.html);
                        
                        // Update pagination dengan format yang lebih rapi
                        if (response.data.total_pages > 1) {
                            const currentPage = parseInt(response.data.current_page);
                            const totalPages = parseInt(response.data.total_pages);
                            let paginationHtml = '';

                            // Tombol Previous
                            if (currentPage > 1) {
                                paginationHtml += `<a href="#" class="prev page-numbers" data-page="${currentPage - 1}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>`;
                            }

                            // Halaman pertama
                            paginationHtml += `<a href="#" class="page-numbers ${currentPage === 1 ? 'current' : ''}" data-page="1">1</a>`;

                            // Ellipsis awal
                            if (currentPage > 3) {
                                paginationHtml += '<span class="page-numbers dots">...</span>';
                            }

                            // Halaman tengah
                            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                                if (i > 1 && i < totalPages) {
                                    paginationHtml += `<a href="#" class="page-numbers ${i === currentPage ? 'current' : ''}" data-page="${i}">${i}</a>`;
                                }
                            }

                            // Ellipsis akhir
                            if (currentPage < totalPages - 2) {
                                paginationHtml += '<span class="page-numbers dots">...</span>';
                            }

                            // Halaman terakhir
                            if (totalPages > 1) {
                                paginationHtml += `<a href="#" class="page-numbers ${currentPage === totalPages ? 'current' : ''}" data-page="${totalPages}">${totalPages}</a>`;
                            }

                            // Tombol Next
                            if (currentPage < totalPages) {
                                paginationHtml += `<a href="#" class="next page-numbers" data-page="${currentPage + 1}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>`;
                            }

                            container.find('.pustakabilitas-pagination').html(paginationHtml);
                        }
                        
                        // Update URL tanpa refresh
                        window.history.pushState({}, '', response.data.pagination_url);
                    }
                },
                complete: function() {
                    loading.hide();
                    container.find('.pustakabilitas-books-grid').removeClass('loading');
                }
            });
        });
    });
}); 