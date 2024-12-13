class PlayerControls {
    constructor() {
        this.player = document.getElementById('audioPlayer');
        this.speedButtons = document.querySelectorAll('.speed-control');
        this.progressBar = document.querySelector('.progress');
        this.currentTime = document.querySelector('.current-time');
        this.duration = document.querySelector('.duration');
        
        this.initializeControls();
    }

    initializeControls() {
        // Speed controls
        this.speedButtons.forEach(button => {
            button.addEventListener('click', () => {
                const speed = parseFloat(button.dataset.speed);
                this.setPlaybackSpeed(speed);
                this.updateSpeedButtons(button);
            });
        });

        // Progress bar updates
        this.player.addEventListener('timeupdate', () => {
            this.updateProgress();
        });

        // Duration display
        this.player.addEventListener('loadedmetadata', () => {
            this.duration.textContent = this.formatTime(this.player.duration);
        });

        // Save and restore playback position
        this.player.addEventListener('timeupdate', () => {
            localStorage.setItem('audioPosition', this.player.currentTime);
        });

        const savedPosition = localStorage.getItem('audioPosition');
        if (savedPosition) {
            this.player.currentTime = parseFloat(savedPosition);
        }
    }

    setPlaybackSpeed(speed) {
        this.player.playbackRate = speed;
    }

    updateSpeedButtons(activeButton) {
        this.speedButtons.forEach(button => {
            button.classList.remove('active');
        });
        activeButton.classList.add('active');
    }

    updateProgress() {
        const progress = (this.player.currentTime / this.player.duration) * 100;
        this.progressBar.style.width = `${progress}%`;
        this.currentTime.textContent = this.formatTime(this.player.currentTime);
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
}

// Initialize controls when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new PlayerControls();
}); 