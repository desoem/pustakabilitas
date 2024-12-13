document.addEventListener('DOMContentLoaded', function() {
    // Handle book downloads
    const downloadButtons = document.querySelectorAll('.download-button');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const bookId = this.dataset.bookId;
            recordStatistics('download', bookId);
        });
    });

    // Handle audio player
    const audioButtons = document.querySelectorAll('.audio-button');
    audioButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.dataset.bookId;
            const audioUrl = this.dataset.audioUrl;
            openDaisyPlayer(bookId, audioUrl);
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

function openDaisyPlayer(bookId, audioUrl) {
    const data = new FormData();
    data.append('action', 'init_daisy_player');
    data.append('book_id', bookId);
    data.append('audio_url', audioUrl);
    data.append('nonce', pustakabilitasAjax.nonce);

    fetch(pustakabilitasAjax.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const playerWindow = window.open('', '_blank', 'width=800,height=600');
            playerWindow.document.write(data.data.html);
            playerWindow.document.close();
        } else {
            console.error('Failed to initialize player');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
} 