document.addEventListener('DOMContentLoaded', function () {
    console.log('Pustakabilitas Frontend Loaded');

    // Statistik unduhan dan pembacaan
    const buttons = document.querySelectorAll('.download-epub-btn, .audio-book-btn');

    buttons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const bookId = this.dataset.bookId;
            const actionType = this.classList.contains('download-epub-btn') ? 'download' : 'read';

            fetch(pustakabilitas.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pustakabilitas_record_statistics',
                    book_id: bookId,
                    action_type: actionType,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`Statistics for ${actionType} recorded successfully.`);
                    if (actionType === 'download') {
                        window.location.href = this.href;
                    } else {
                        window.open(this.href, '_blank');
                    }
                } else {
                    console.error('Failed to record statistics');
                }
            });
        });
    });

    // Statistik buku audio
    const audioPlayer = document.getElementById('daisy-player');
    if (audioPlayer) {
        audioPlayer.addEventListener('play', function () {
            console.log('Buku Audio dimulai.');

            const bookId = audioPlayer.getAttribute('data-book-id'); // Mendapatkan book_id

            // Mengirimkan statistik buku audio
            fetch('/wp-json/pustakabilitas/v1/book-statistics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_id: bookId,
                    action: 'read'
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Statistik buku audio berhasil dicatat.');
                } else {
                    console.error('Gagal mencatat statistik buku audio');
                }
            });
        });
    }
});
