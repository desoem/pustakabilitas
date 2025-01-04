document.addEventListener('DOMContentLoaded', function() {
    // Book Statistics Handler
    class BookStatistics {
        constructor() {
            this.initializeEventListeners();
        }

        initializeEventListeners() {
            // Download & Read buttons
            document.querySelectorAll('.download-epub-btn, .audio-book-btn')
                .forEach(button => button.addEventListener('click', this.handleAction.bind(this)));
            
            // Audio player
            const audioPlayer = document.getElementById('daisy-player');
            if (audioPlayer) {
                audioPlayer.addEventListener('play', this.handleAudioPlay.bind(this));
            }
        }

        async handleAction(e) {
            e.preventDefault();
            const button = e.currentTarget;
            const bookId = button.dataset.bookId;
            const actionType = button.classList.contains('download-epub-btn') ? 'download' : 'read';

            try {
                await this.recordStatistics(bookId, actionType);
                if (actionType === 'download') {
                    window.location.href = button.href;
                } else {
                    window.open(button.href, '_blank');
                }
            } catch (error) {
                console.error('Failed to record statistics:', error);
            }
        }

        async recordStatistics(bookId, actionType) {
            const response = await fetch(pustakabilitas.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'pustakabilitas_record_statistics',
                    book_id: bookId,
                    action_type: actionType
                })
            });
            
            const data = await response.json();
            if (!data.success) throw new Error('Failed to record statistics');
            return data;
        }
    }

    // Initialize
    new BookStatistics();
});
