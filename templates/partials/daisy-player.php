<div class="daisy-player-wrapper">
    <div class="player-header">
        <h2><?php echo esc_html($metadata['title']); ?></h2>
        <div class="book-info">
            <span class="author"><?php echo esc_html($metadata['author']); ?></span>
        </div>
    </div>

    <div class="player-controls">
        <audio id="audioPlayer" controls>
            <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
            <?php _e('Your browser does not support the audio element.', 'pustakabilitas'); ?>
        </audio>

        <div class="control-buttons">
            <button class="speed-control" data-speed="0.75">0.75x</button>
            <button class="speed-control" data-speed="1" class="active">1x</button>
            <button class="speed-control" data-speed="1.25">1.25x</button>
            <button class="speed-control" data-speed="1.5">1.5x</button>
        </div>

        <div class="progress-info">
            <span class="current-time">00:00</span>
            <div class="progress-bar">
                <div class="progress"></div>
            </div>
            <span class="duration">00:00</span>
        </div>
    </div>
</div> 