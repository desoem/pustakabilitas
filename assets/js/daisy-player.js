// Add debug mode
const DEBUG = true;

function debugLog(...args) {
    if (DEBUG) {
        console.log('[Pustakabilitas]', ...args);
    }
}

class DaisyPlayer {
    constructor() {
        this.audioPlayer = document.getElementById('audioPlayer');
        this.progressBar = document.querySelector('.progress');
        this.currentTime = document.querySelector('.current-time');
        this.duration = document.querySelector('.duration');
        this.speedControls = document.querySelectorAll('.speed-control');
        
        if (this.audioPlayer) {
            this.initializePlayer();
        }

        // Tambahkan event listeners untuk debugging
        if (DEBUG) {
            this.addDebugListeners();
        }
    }

    addDebugListeners() {
        this.audioPlayer.addEventListener('error', (e) => {
            debugLog('Audio Error:', e);
            debugLog('Error code:', this.audioPlayer.error.code);
            debugLog('Error message:', this.audioPlayer.error.message);
        });

        this.audioPlayer.addEventListener('stalled', () => {
            debugLog('Audio Stalled at:', this.audioPlayer.currentTime);
        });

        this.audioPlayer.addEventListener('waiting', () => {
            debugLog('Audio Waiting at:', this.audioPlayer.currentTime);
        });

        this.audioPlayer.addEventListener('suspend', () => {
            debugLog('Audio Suspended at:', this.audioPlayer.currentTime);
        });

        // Monitor buffering
        this.audioPlayer.addEventListener('progress', () => {
            const buffered = this.audioPlayer.buffered;
            if (buffered.length > 0) {
                debugLog('Buffered ranges:');
                for (let i = 0; i < buffered.length; i++) {
                    debugLog(`Range ${i}: ${buffered.start(i)} to ${buffered.end(i)}`);
                }
            }
        });
    }

    initializePlayer() {
        // Initialize event listeners
        this.audioPlayer.addEventListener('loadedmetadata', () => this.updateDuration());
        this.audioPlayer.addEventListener('timeupdate', () => this.updateProgress());
        this.audioPlayer.addEventListener('ended', () => this.handleEnded());

        // Speed control events
        this.speedControls.forEach(control => {
            control.addEventListener('click', (e) => this.changePlaybackSpeed(e));
        });

        // Progress bar click event
        document.querySelector('.progress-bar').addEventListener('click', (e) => this.seek(e));

        // Keyboard controls
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));

        // Tambahkan preload attribute
        this.audioPlayer.preload = 'auto';

        // Tambahkan buffer checking
        setInterval(() => this.checkBuffer(), 1000);
    }

    updateDuration() {
        this.duration.textContent = this.formatTime(this.audioPlayer.duration);
    }

    updateProgress() {
        const progress = (this.audioPlayer.currentTime / this.audioPlayer.duration) * 100;
        this.progressBar.style.width = `${progress}%`;
        this.currentTime.textContent = this.formatTime(this.audioPlayer.currentTime);

        // Tambahkan log untuk monitoring
        if (DEBUG && this.audioPlayer.currentTime >= 30) {
            debugLog('Current Time:', this.audioPlayer.currentTime);
            debugLog('Buffer State:', this.getBufferState());
        }
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    changePlaybackSpeed(event) {
        const speed = parseFloat(event.target.dataset.speed);
        this.audioPlayer.playbackRate = speed;

        // Update active state
        this.speedControls.forEach(control => {
            control.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    seek(event) {
        const progressBar = event.currentTarget;
        const rect = progressBar.getBoundingClientRect();
        const pos = (event.clientX - rect.left) / rect.width;
        this.audioPlayer.currentTime = pos * this.audioPlayer.duration;
    }

    handleKeyboard(event) {
        switch(event.code) {
            case 'Space':
                event.preventDefault();
                this.togglePlay();
                break;
            case 'ArrowLeft':
                event.preventDefault();
                this.skipBackward();
                break;
            case 'ArrowRight':
                event.preventDefault();
                this.skipForward();
                break;
        }
    }

    togglePlay() {
        if (this.audioPlayer.paused) {
            this.audioPlayer.play();
        } else {
            this.audioPlayer.pause();
        }
    }

    skipBackward() {
        this.audioPlayer.currentTime = Math.max(0, this.audioPlayer.currentTime - 10);
    }

    skipForward() {
        this.audioPlayer.currentTime = Math.min(
            this.audioPlayer.duration,
            this.audioPlayer.currentTime + 10
        );
    }

    handleEnded() {
        // Record completion in statistics
        const bookId = this.audioPlayer.dataset.bookId;
        if (bookId) {
            this.recordCompletion(bookId);
        }
    }

    recordCompletion(bookId) {
        const data = new FormData();
        data.append('action', 'pustakabilitas_record_statistics');
        data.append('book_id', bookId);
        data.append('action_type', 'read');
        data.append('nonce', pustakabilitasAjax.nonce);

        fetch(pustakabilitasAjax.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        });
    }

    checkBuffer() {
        if (!this.audioPlayer.paused) {
            const buffered = this.audioPlayer.buffered;
            const currentTime = this.audioPlayer.currentTime;
            
            if (buffered.length > 0) {
                let bufferedEnd = 0;
                for (let i = 0; i < buffered.length; i++) {
                    if (currentTime >= buffered.start(i) && currentTime <= buffered.end(i)) {
                        bufferedEnd = buffered.end(i);
                        break;
                    }
                }

                // Jika buffer terlalu dekat dengan posisi pemutaran
                if (bufferedEnd - currentTime < 5) { // 5 detik threshold
                    debugLog('Buffer running low:', bufferedEnd - currentTime);
                    // Coba reload source jika buffer hampir habis
                    this.reloadAudioSource();
                }
            }
        }
    }

    reloadAudioSource() {
        const currentTime = this.audioPlayer.currentTime;
        const wasPlaying = !this.audioPlayer.paused;
        const currentSrc = this.audioPlayer.src;

        debugLog('Reloading audio source at:', currentTime);

        this.audioPlayer.src = currentSrc;
        this.audioPlayer.load();
        this.audioPlayer.currentTime = currentTime;
        
        if (wasPlaying) {
            this.audioPlayer.play().catch(e => debugLog('Play failed:', e));
        }
    }

    getBufferState() {
        const buffered = this.audioPlayer.buffered;
        const ranges = [];
        
        for (let i = 0; i < buffered.length; i++) {
            ranges.push({
                start: buffered.start(i),
                end: buffered.end(i)
            });
        }
        
        return ranges;
    }
}

// Initialize player when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DaisyPlayer();
}); 