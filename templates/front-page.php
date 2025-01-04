<?php
/**
 * Template Name: Pustakabilitas Front Page
 * 
 * This template can be edited with Elementor
 */

// Import Elementor namespace
use Elementor\Plugin;

get_header(); // Gunakan header theme default

// Check if Elementor is active
if (!did_action('elementor/loaded')) {
    // Elementor tidak aktif, tampilkan pesan error atau lanjutkan dengan template default
    error_log('Elementor is not active. Please install and activate Elementor plugin.');
}

// Inisialisasi variabel statistik dengan nilai default
$total_books = 0;
$total_users = 0;
$total_reads = 0;
$total_downloads = 0;
$total_visitors = 0;

try {
    // Mengambil data statistik
    $total_books = get_total_books();
    $total_users = get_total_users();
    $total_reads = get_total_reads();
    $total_downloads = get_total_downloads();
    $total_visitors = get_total_visitors();
} catch (Exception $e) {
    error_log('Pustakabilitas Statistics Error: ' . $e->getMessage());
}

?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();
            
            // Cek apakah Elementor aktif dan halaman diedit dengan Elementor
            if (class_exists('\Elementor\Plugin')) {
                $elementor_instance = Plugin::instance();
                if ($elementor_instance->editor->is_edit_mode() || $elementor_instance->preview->is_preview_mode()) {
                    the_content();
                } else {
                    // Tampilan default jika tidak menggunakan Elementor
                    ?>
                    <div class="pustakabilitas-front-page">
                        <!-- Hero Section -->
                        <section class="hero-section">
                            <div class="container">
                                <h1><?php _e('Welcome to Digital Library', 'pustakabilitas'); ?></h1>
                                <p class="tagline"><?php echo get_theme_mod('pustakabilitas_tagline', __('Access to knowledge for everyone', 'pustakabilitas')); ?></p>
                                
                                <!-- Search Form -->
                                <div class="main-search-form" role="search">
                                    <?php echo do_shortcode('[pustakabilitas_search_form]'); ?>
                                </div>

                                <!-- Statistics -->
                                <div class="statistics-section">
                                    <?php echo do_shortcode('[pustakabilitas_statistics]'); ?>
                                </div>
                            </div>
                        </section>

                        <!-- Latest Books Section -->
                        <section class="books-section latest-books">
                            <div class="container">
                                <?php echo do_shortcode('[pustakabilitas_latest_books]'); ?>
                            </div>
                        </section>

                        <!-- Popular Books Section -->
                        <section class="books-section popular-books">
                            <div class="container">
                                <?php echo do_shortcode('[pustakabilitas_popular_books]'); ?>
                            </div>
                        </section>
                    </div>
                    <?php
                }
            } else {
                // Fallback jika Elementor tidak aktif
                the_content();
            }
        endwhile;
        ?>
    </main>
</div>

<?php
get_footer(); 