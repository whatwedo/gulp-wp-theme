<?php
/**
 * local-config.php Generator
 */

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
if (file_exists(__DIR__ . '/local-config.php')) {
    die('local-config.php already exists.');
}

$contents = [];

$contents[] = '<?php';
$contents[] = '';
$contents[] = 'define(\'DB_NAME\',     \'gulp-wp-theme\');';
$contents[] = 'define(\'DB_USER\',     \'root\');';
$contents[] = 'define(\'DB_PASSWORD\', \'root\');';
$contents[] = 'define(\'DB_HOST\',     \'localhost\');';
$contents[] = '';
$contents[] = '$table_prefix =       \'wp_\';';
$contents[] = '';
$contents[] = 'define(\'WP_DEBUG\',    false);';
$contents[] = '';
$contents[] = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');

file_put_contents(__DIR__ . '/local-config.php', implode(PHP_EOL, $contents));

echo PHP_EOL . 'please insert your database details in dist/local-config.php' . PHP_EOL;
