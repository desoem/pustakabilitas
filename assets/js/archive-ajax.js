document.addEventListener('DOMContentLoaded', function() {
    const booksGrid = document.querySelector('.books-grid');
    const pagination = document.querySelector('.pagination[data-ajax="true"]');

    if (pagination) {
        pagination.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link) {
                e.preventDefault();
                
                fetch(link.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newBooks = doc.querySelector('.books-grid').innerHTML;
                        const newPagination = doc.querySelector('.pagination').innerHTML;
                        
                        booksGrid.innerHTML = newBooks;
                        pagination.innerHTML = newPagination;
                        
                        // Update URL without page reload
                        history.pushState({}, '', link.href);
                        
                        // Scroll to top of books grid
                        booksGrid.scrollIntoView({ behavior: 'smooth' });
                    });
            }
        });
    }
}); 