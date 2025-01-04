<?php
// Prevent direct access to this directory
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access forbidden.');
}

// Redirect to the main Daisy Web Player
header('Location: dwp.html');
exit;