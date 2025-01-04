<?php
/*
 * cors_proxy.php - Perform cross-platform requests with jquery
 *
 * Usage: proxy.php?url=mysite.cross-domain.com/myfile.html
 *
 * -> GET parameter:
 *
 * url : requested url
 * 
 * <- RETURNS:
 *
 * ERROR:   '' (nothing) in case of an error (no url, or no ajax initiated request)
 * SUCCESS: url content
 * 
 * References: http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
 */
$url = $_GET['url'] or die('');

if (get_headers($url, 1)[0] != 'HTTP/1.1 200 OK') 
    die(''); 
    echo file_get_contents($url)
?>
