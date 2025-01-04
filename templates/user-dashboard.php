<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$user = get_userdata($user_id);
?>

<style>
/* Container utama */
.pustakabilitas-dashboard {
    padding: 0; /* Hapus padding untuk mobile */
    max-width: 100%;
    margin: 0 auto;
}

/* Header Dashboard */
.dashboard-header {
    padding: 10px 15px; /* Tambahkan padding kiri-kanan, kurangi padding atas-bawah */
}

.dashboard-welcome {
    font-size: 1.5rem; /* Kurangi ukuran font di mobile */
    margin: 0 0 15px; /* Kurangi margin bawah */
}

/* Responsive padding untuk tablet dan desktop */
@media (min-width: 768px) {
    .pustakabilitas-dashboard {
        padding: 15px;
    }
    
    .dashboard-welcome {
        font-size: 1.8rem;
    }
}

@media (min-width: 1024px) {
    .pustakabilitas-dashboard {
        padding: 20px;
        max-width: 1200px;
    }
}
</style>

<div class="pustakabilitas-dashboard">
    <header class="dashboard-header">
        <h1 class="dashboard-welcome">
            <?php printf(__('Selamat datang, %s!', 'pustakabilitas'), $user->display_name); ?>
        </h1>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="dashicons dashicons-book-alt" aria-hidden="true"></i>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $this->get_user_book_count('read'); ?></span>
                    <span class="stat-label"><?php _e('Buku Dibaca', 'pustakabilitas'); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <i class="dashicons dashicons-bookmark" aria-hidden="true"></i>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $this->get_user_bookmark_count(); ?></span>
                    <span class="stat-label"><?php _e('Bookmark', 'pustakabilitas'); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <i class="dashicons dashicons-download" aria-hidden="true"></i>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $this->get_user_book_count('downloaded'); ?></span>
                    <span class="stat-label"><?php _e('Buku Diunduh', 'pustakabilitas'); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div id="dashboard-tabs">
        <nav class="tab-navigation" role="tablist">
            <button id="tab-1" class="tab-button active" role="tab" aria-selected="true" aria-controls="panel-1">
                <i class="dashicons dashicons-book-alt" aria-hidden="true"></i>
                <span><?php _e('Koleksi Buku', 'pustakabilitas'); ?></span>
            </button>
            <button id="tab-2" class="tab-button" role="tab" aria-selected="false" aria-controls="panel-2">
                <i class="dashicons dashicons-clock" aria-hidden="true"></i>
                <span><?php _e('Riwayat Baca', 'pustakabilitas'); ?></span>
            </button>
            <button id="tab-3" class="tab-button" role="tab" aria-selected="false" aria-controls="panel-3">
                <i class="dashicons dashicons-bookmark" aria-hidden="true"></i>
                <span><?php _e('Bookmark', 'pustakabilitas'); ?></span>
            </button>
            <button id="tab-4" class="tab-button" role="tab" aria-selected="false" aria-controls="panel-4">
                <i class="dashicons dashicons-download" aria-hidden="true"></i>
                <span><?php _e('Unduhan', 'pustakabilitas'); ?></span>
            </button>
        </nav>

        <div class="tab-panels">
            <!-- Koleksi Buku -->
            <div id="panel-1" class="tab-panel active" role="tabpanel" aria-labelledby="tab-1">
                <?php include(PUSTAKABILITAS_PLUGIN_DIR . 'templates/dashboard/tab-my-books.php'); ?>
            </div>

            <!-- Riwayat Baca -->
            <div id="panel-2" class="tab-panel" role="tabpanel" aria-labelledby="tab-2">
                <?php include(PUSTAKABILITAS_PLUGIN_DIR . 'templates/dashboard/tab-reading-history.php'); ?>
            </div>

            <!-- Bookmark -->
            <div id="panel-3" class="tab-panel" role="tabpanel" aria-labelledby="tab-3">
                <?php include(PUSTAKABILITAS_PLUGIN_DIR . 'templates/dashboard/tab-bookmarks.php'); ?>
            </div>

            <!-- Unduhan -->
            <div id="panel-4" class="tab-panel" role="tabpanel" aria-labelledby="tab-4">
                <?php include(PUSTAKABILITAS_PLUGIN_DIR . 'templates/dashboard/tab-downloads.php'); ?>
            </div>
        </div>
    </div>
</div>
