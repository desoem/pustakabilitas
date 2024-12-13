<?php
/*
Template Name: DAISY Player
*/

// Validasi parameter
$book_url = isset($_GET['book']) ? esc_url_raw($_GET['book']) : '';
$title = isset($_GET['title']) ? sanitize_text_field($_GET['title']) : '';

if (empty($book_url)) {
    wp_die(__('Invalid book URL', 'pustakabilitas'));
}

// Tampilkan hanya player
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($title); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .daisy-player-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .player-header {
            margin-bottom: 20px;
            text-align: center;
        }
        audio {
            width: 100%;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="daisy-player-container">
        <div class="player-header">
            <h1><?php echo esc_html($title); ?></h1>
        </div>
        
        <audio id="daisyPlayer" controls>
            <source src="<?php echo esc_url($book_url); ?>" type="audio/mpeg">
            <?php _e('Your browser does not support the audio element.', 'pustakabilitas'); ?>
        </audio>
    </div>
    <?php wp_footer(); ?>
</body>
</html> 