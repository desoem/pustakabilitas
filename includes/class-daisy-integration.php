<?php
/**
 * Daisy Player Integration
 */

class Pustakabilitas_Daisy_Integration {
    private $plugin_path;
    private $plugin_url;

    public function __construct() {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_daisy_scripts']);
        add_filter('the_content', [$this, 'maybe_add_daisy_player']);
    }

    /**
     * Enqueue necessary scripts for Daisy Player
     */
    public function enqueue_daisy_scripts() {
        if (is_singular('pustakabilitas_book')) {
            wp_enqueue_script(
                'pustakabilitas-daisy-player',
                $this->plugin_url . 'assets/js/daisy-player.js',
                ['jquery'],
                '1.0.0',
                true
            );

            // Localize script
            wp_localize_script('pustakabilitas-daisy-player', 'pustakabilitasDaisy', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pustakabilitas-daisy-nonce')
            ]);
        }
    }

    /**
     * Add Daisy player if book is audio type
     */
    public function maybe_add_daisy_player($content) {
        // Only add player on single book pages
        if (!is_singular('pustakabilitas_book')) {
            return $content;
        }

        // Check if it's an audio book
        $book_type = get_post_meta(get_the_ID(), '_pustakabilitas_book_type', true);
        if ($book_type !== 'audio') {
            return $content;
        }

        return $content;
    }

    /**
     * Initialize Daisy Player in new window
     */
    public static function init_daisy_player($audio_url, $title, $ncc_path, $smil_path) {
        // Initialize handlers
        $bookmark_handler = new Pustakabilitas_Bookmark_Handler(get_the_ID(), get_current_user_id());
        $production_notes = new Pustakabilitas_Production_Notes(get_the_ID());
        
        $bookmarks = $bookmark_handler->get_bookmarks();
        $prod_notes = $production_notes->get_production_notes();

        // Initialize parsers
        $ncc_parser = new Pustakabilitas_Daisy_Parser($ncc_path);
        $smil_parser = new Pustakabilitas_Smil_Parser($smil_path);
        
        $book_data = $ncc_parser->get_book_data();
        $segments = $smil_parser->get_segments();

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>DAISY Player - <?php echo esc_html($title); ?></title>
            <meta charset="UTF-8">
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    background: #f5f5f5;
                }
                .player-container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                /* Tab Navigation */
                .tab-nav {
                    display: flex;
                    border-bottom: 2px solid #ddd;
                    margin-bottom: 20px;
                }
                .tab-button {
                    padding: 10px 20px;
                    background: none;
                    border: none;
                    border-bottom: 2px solid transparent;
                    margin-bottom: -2px;
                    cursor: pointer;
                    color: #666;
                    font-weight: 500;
                }
                .tab-button.active {
                    color: #0073aa;
                    border-bottom-color: #0073aa;
                }
                .tab-button:hover {
                    color: #0073aa;
                }
                /* Tab Content */
                .tab-content {
                    display: none;
                    padding: 20px 0;
                }
                .tab-content.active {
                    display: block;
                }
                /* Table of Contents */
                .toc-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }
                .toc-item {
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                    cursor: pointer;
                }
                .toc-item:hover {
                    color: #0073aa;
                }
                /* Bookmarks */
                .bookmark-list {
                    list-style: none;
                    padding: 0;
                }
                .bookmark-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px;
                    border-bottom: 1px solid #eee;
                }
                /* Player Controls */
                .player-controls {
                    margin: 20px 0;
                }
                .audio-player {
                    width: 100%;
                    margin-bottom: 15px;
                }
                .control-buttons {
                    display: flex;
                    gap: 10px;
                    justify-content: center;
                }
                button {
                    padding: 8px 16px;
                    background: #0073aa;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                button:hover {
                    background: #005177;
                }
                .text-content {
                    padding: 20px;
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin: 20px 0;
                    max-height: 300px;
                    overflow-y: auto;
                }
                .text-highlight {
                    background-color: #ffeb3b;
                }
                .navigation-level {
                    margin: 10px 0;
                }
                .level-button {
                    padding: 5px 10px;
                    margin: 0 5px;
                    background: #eee;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                }
                .level-button.active {
                    background: #0073aa;
                    color: white;
                }
                .bookmark-panel {
                    padding: 15px;
                    background: #fff;
                    border: 1px solid #ddd;
                    margin: 10px 0;
                }
                .bookmark-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px;
                    border-bottom: 1px solid #eee;
                }
                .bookmark-notes {
                    font-size: 0.9em;
                    color: #666;
                    margin-top: 5px;
                }
                .production-note {
                    background: #fff3cd;
                    padding: 10px;
                    margin: 5px 0;
                    border-left: 3px solid #ffeeba;
                }
                .reading-progress {
                    height: 4px;
                    background: #eee;
                    margin: 10px 0;
                }
                .progress-bar {
                    height: 100%;
                    background: #0073aa;
                    width: 0;
                }
                .shortcuts-panel {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: rgba(0,0,0,0.8);
                    color: white;
                    padding: 10px;
                    border-radius: 4px;
                    display: none;
                }
            </style>
        </head>
        <body>
            <div class="player-container">
                <!-- Navigation Levels -->
                <div class="navigation-level">
                    <button class="level-button" onclick="setLevel(1)">Level 1</button>
                    <button class="level-button" onclick="setLevel(2)">Level 2</button>
                    <button class="level-button" onclick="setLevel(3)">Level 3</button>
                    <span class="current-level">Current Level: <span id="levelDisplay">1</span></span>
                </div>

                <!-- Text Display -->
                <div class="text-content" id="textDisplay"></div>

                <!-- Audio Player -->
                <audio id="daisyPlayer" controls>
                    <source src="<?php echo esc_url($audio_url); ?>" type="audio/mpeg">
                </audio>

                <!-- Navigation Controls -->
                <div class="navigation-controls">
                    <button onclick="previousSection()">Previous Section</button>
                    <button onclick="nextSection()">Next Section</button>
                    <button onclick="togglePlay()">Play/Pause</button>
                    <button onclick="adjustSpeed(-0.25)">Slower</button>
                    <button onclick="adjustSpeed(0.25)">Faster</button>
                </div>

                <!-- Tabs Content -->
                <div class="tab-content">
                    <!-- ... previous tab content ... -->
                </div>

                <!-- Reading Progress -->
                <div class="reading-progress">
                    <div class="progress-bar" id="progressBar"></div>
                </div>

                <!-- Bookmarks Panel -->
                <div class="bookmark-panel">
                    <h3>Bookmarks</h3>
                    <div id="bookmarksList">
                        <?php foreach ($bookmarks as $bookmark): ?>
                            <div class="bookmark-item" data-id="<?php echo esc_attr($bookmark['id']); ?>">
                                <div>
                                    <strong><?php echo esc_html($bookmark['title']); ?></strong>
                                    <div class="bookmark-notes"><?php echo esc_html($bookmark['notes']); ?></div>
                                </div>
                                <div>
                                    <button onclick="jumpToBookmark('<?php echo esc_attr($bookmark['position']); ?>')">Jump</button>
                                    <button onclick="deleteBookmark('<?php echo esc_attr($bookmark['id']); ?>')">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button onclick="addBookmark()">Add Bookmark</button>
                </div>

                <!-- Production Notes -->
                <div class="production-notes">
                    <h3>Production Notes</h3>
                    <?php foreach ($prod_notes as $note): ?>
                        <div class="production-note">
                            <div class="note-time"><?php echo esc_html($note['time_code']); ?></div>
                            <div class="note-content"><?php echo esc_html($note['content']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Shortcuts Help -->
                <div class="shortcuts-panel" id="shortcutsPanel">
                    <h4>Keyboard Shortcuts</h4>
                    <ul>
                        <li>Space: Play/Pause</li>
                        <li>Left/Right: Skip 10s</li>
                        <li>Up/Down: Volume</li>
                        <li>B: Add Bookmark</li>
                        <li>M: Show/Hide Menu</li>
                        <li>?: Show/Hide Shortcuts</li>
                    </ul>
                </div>
            </div>

            <script>
                const player = document.getElementById('daisyPlayer');
                const speedDisplay = document.getElementById('speedValue');
                const currentTimeDisplay = document.getElementById('currentTime');
                const durationDisplay = document.getElementById('duration');
                let currentSpeed = 1.0;

                // Tab Navigation
                function openTab(tabName) {
                    const tabs = document.getElementsByClassName('tab-content');
                    const buttons = document.getElementsByClassName('tab-button');
                    
                    Array.from(tabs).forEach(tab => {
                        tab.classList.remove('active');
                    });
                    Array.from(buttons).forEach(button => {
                        button.classList.remove('active');
                    });

                    document.getElementById(tabName).classList.add('active');
                    event.currentTarget.classList.add('active');
                }

                    if (currentSegment) {
                        // Highlight current text
                        const textElements = document.querySelectorAll('.text-content p');
                        textElements.forEach(el => el.classList.remove('text-highlight'));
                        
                        const currentText = document.getElementById(currentSegment.text_id);
                        if (currentText) {
                            currentText.classList.add('text-highlight');
                            currentText.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                }

                // Navigation
                function setLevel(level) {
                    currentLevel = level;
                    document.getElementById('levelDisplay').textContent = level;
                    updateNavigation();
                }

                function updateNavigation() {
                    // Filter sections based on current level
                    const levelSections = bookData.sections.filter(section => 
                        section.level === currentLevel
                    );
                    // Update navigation UI
                }

                function previousSection() {
                    const currentTime = player.currentTime;
                    const previousSegment = segments.reverse().find(segment => 
                        segment.begin < currentTime
                    );
                    if (previousSegment) {
                        player.currentTime = previousSegment.begin;
                    }
                }

                function nextSection() {
                    const currentTime = player.currentTime;
                    const nextSegment = segments.find(segment => 
                        segment.begin > currentTime
                    );
                    if (nextSegment) {
                        player.currentTime = nextSegment.begin;
                    }
                }

                // Playback Control
                function togglePlay() {
                    if (player.paused) {
                        player.play();
                    } else {
                        player.pause();
                    }
                }

                // Initialize
                player.addEventListener('loadedmetadata', function() {
                    durationDisplay.textContent = formatTime(player.duration);
                    loadBookmarks();
                });

                player.addEventListener('timeupdate', function() {
                    localStorage.setItem('daisy-position', player.currentTime);
                });

                // Load saved position
                const savedPosition = localStorage.getItem('daisy-position');
                if (savedPosition) {
                    player.currentTime = parseFloat(savedPosition);
                }

                // Bookmark functions
                function addBookmark() {
                    const currentTime = player.currentTime;
                    const title = prompt('Enter bookmark title:');
                    const notes = prompt('Enter any notes:');
                    
                    if (title) {
                        fetch('/wp-admin/admin-ajax.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'save_bookmark',
                                book_id: <?php echo get_the_ID(); ?>,
                                position: currentTime,
                                title: title,
                                notes: notes,
                                section: getCurrentSection(),
                                level: currentLevel,
                                nonce: '<?php echo wp_create_nonce('save_bookmark'); ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                updateBookmarksList();
                            }
                        });
                    }
                }

                // Reading Progress
                function updateProgress() {
                    const progress = (player.currentTime / player.duration) * 100;
                    document.getElementById('progressBar').style.width = `${progress}%`;
                    
                    // Save progress
                    saveReadingProgress(progress);
                }

                // Keyboard Shortcuts
                document.addEventListener('keydown', function(e) {
                    if (e.key === '?') {
                        toggleShortcutsPanel();
                    } else if (e.key === 'b') {
                        addBookmark();
                    }
                    // Previous shortcuts...
                });

                // Initialize
                player.addEventListener('timeupdate', function() {
                    updateProgress();
                    updateTextDisplay(player.currentTime);
                });

                // Load saved progress
                loadReadingProgress();
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public static function init_ajax_handlers() {
        add_action('wp_ajax_init_daisy_player', [self::class, 'handle_init_daisy_player']);
        add_action('wp_ajax_nopriv_init_daisy_player', [self::class, 'handle_init_daisy_player']);
    }

    public static function handle_init_daisy_player() {
        check_ajax_referer('init_daisy_player', 'nonce');

        $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
        $audio_url = isset($_POST['audio_url']) ? esc_url_raw($_POST['audio_url']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

        // Validate URL and file type
        $file_type = strtolower(pathinfo($audio_url, PATHINFO_EXTENSION));
        
        if (!in_array($file_type, ['epub', 'mp3', 'daisy'])) {
            wp_send_json_error('Unsupported file format');
            return;
        }

        try {
            // Generate player HTML based on file type
            if ($file_type === 'epub') {
                $player_html = self::generate_epub_player_html($audio_url, $title, $book_id);
            } else {
                $player_html = self::generate_audio_player_html($audio_url, $title, $book_id);
            }
            
            wp_send_json_success([
                'html' => $player_html,
                'type' => $file_type
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Error initializing player: ' . $e->getMessage());
        }
    }

    private static function generate_epub_player_html($epub_url, $title, $book_id) {
        ob_start();
        ?>
        <div class="daisy-player-container">
            <div class="player-header">
                <h2><?php echo esc_html($title); ?></h2>
            </div>

            <div class="player-controls">
                <audio id="daisyPlayer" controls>
                    <source src="<?php echo esc_url($epub_url); ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                
                <div class="navigation-controls">
                    <button class="nav-button" onclick="previousChapter()">
                        <span class="dashicons dashicons-controls-back"></span> Previous
                    </button>
                    <button class="nav-button" onclick="playPause()">
                        <span class="dashicons dashicons-controls-play"></span> Play/Pause
                    </button>
                    <button class="nav-button" onclick="nextChapter()">
                        <span class="dashicons dashicons-controls-forward"></span> Next
                    </button>
                </div>

                <div class="playback-controls">
                    <button onclick="adjustSpeed(-0.25)">Slower</button>
                    <span id="speedDisplay">1x</span>
                    <button onclick="adjustSpeed(0.25)">Faster</button>
                </div>
            </div>

            <div class="content-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="chapters">Chapters</button>
                    <button class="tab-button" data-tab="bookmarks">Bookmarks</button>
                    <button class="tab-button" data-tab="info">Info</button>
                </div>

                <div class="tab-content active" id="chapters">
                    <div id="chaptersList" class="chapters-list">
                        Loading chapters...
                    </div>
                </div>

                <div class="tab-content" id="bookmarks">
                    <div id="bookmarksList" class="bookmarks-list"></div>
                    <button class="add-bookmark-btn" onclick="addBookmark()">
                        <span class="dashicons dashicons-bookmark"></span> Add Bookmark
                    </button>
                </div>

                <div class="tab-content" id="info">
                    <dl class="book-info">
                        <dt>Title</dt>
                        <dd><?php echo esc_html($title); ?></dd>
                        <dt>Format</dt>
                        <dd>EPUB Audio</dd>
                    </dl>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            initAudioPlayer({
                bookId: <?php echo json_encode($book_id); ?>,
                audioUrl: <?php echo json_encode($epub_url); ?>,
                title: <?php echo json_encode($title); ?>
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    private static function generate_audio_player_html($audio_url, $title, $book_id) {
        // Existing audio player HTML generation code...
    }
}

// Initialize AJAX handlers
add_action('init', ['Pustakabilitas_Daisy_Integration', 'init_ajax_handlers']);

new Pustakabilitas_Daisy_Integration();
