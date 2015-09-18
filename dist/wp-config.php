<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

if (file_exists(dirname(__FILE__) . '/local-config.php'))
    require(dirname(__FILE__) . '/local-config.php');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
if ( !defined('DB_NAME') )
    define('DB_NAME', 'database_name_here');

/** MySQL database username */
if ( !defined('DB_USER') )
    define('DB_USER', 'username_here');

/** MySQL database password */
if ( !defined('DB_PASSWORD') )
    define('DB_PASSWORD', 'password_here');

/** MySQL hostname */
if ( !defined('DB_HOST') )
    define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
if ( !defined('DB_CHARSET') )
    define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
if ( !defined('DB_COLLATE') )
    define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
if ( !defined('AUTH_KEY') )
    define('AUTH_KEY',         'put your unique phrase here');
if ( !defined('SECURE_AUTH_KEY') )
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
if ( !defined('LOGGED_IN_KEY') )
    define('LOGGED_IN_KEY',    'put your unique phrase here');
if ( !defined('NONCE_KEY') )
    define('NONCE_KEY',        'put your unique phrase here');
if ( !defined('AUTH_SALT') )
    define('AUTH_SALT',        'put your unique phrase here');
if ( !defined('SECURE_AUTH_SALT') )
    define('SECURE_AUTH_SALT', 'put your unique phrase here');
if ( !defined('LOGGED_IN_SALT') )
    define('LOGGED_IN_SALT',   'put your unique phrase here');
if ( !defined('NONCE_SALT') )
    define('NONCE_SALT',       'put your unique phrase here');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
if (!isset($table_prefix))
    $table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
if ( !defined('WP_DEBUG') )
    define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');