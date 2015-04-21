<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// Include local configuration
if (file_exists(dirname(__FILE__) . '/local-config.php')) {
  include(dirname(__FILE__) . '/local-config.php');
}

// Global DB config
if (!defined('DB_NAME')) {
  define('DB_NAME', 'healthslam');
}
if (!defined('DB_USER')) {
  define('DB_USER', 'root');
}
if (!defined('DB_PASSWORD')) {
  define('DB_PASSWORD', '');
}
if (!defined('DB_HOST')) {
  define('DB_HOST', 'localhost');
}

/** Database Charset to use in creating database tables. */
if (!defined('DB_CHARSET')) {
  define('DB_CHARSET', 'utf8');
}

/** The Database Collate type. Don't change this if in doubt. */
if (!defined('DB_COLLATE')) {
  define('DB_COLLATE', '');
}

/** Disable internal wp-cron function */
define('DISABLE_WP_CRON', false);

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '7<tmW6guw8U0h8`bj%w;|Cb2DHKf|5(;==9@EIfW!)%,vFs~JZe(YHR?At|d}z=,');
define('SECURE_AUTH_KEY',  'H4k-2pLw|iJVnx1;j/:y$?c0|COe&4O|X!hYyL*o53i=.0gkO*zJKY|U_&J}O| S');
define('LOGGED_IN_KEY',    '=Lt(!fC{e|S8X[QH.;eCx$ZkwwO?j,|zhHP+e PG8r?|WY<Z+#!bq]0+`^*i+TZG');
define('NONCE_KEY',        '&EhJ/|vPpguxIs?JDuB$;7[`4)z#vtWl^.,pJtq`x(F?[<Ru Dw27k3h!C9p,4=]');
define('AUTH_SALT',        'ULV(5-s||@3dDCcG#WEYc2|-mKTH~=6qv9t]lZK<<zWWD.Q*h1Y+*,+IF-IIU{Is');
define('SECURE_AUTH_SALT', 'Q~fSp8HNXOLL=%7H(hBsjc1q1.W)+hf?BbXcwO`sC3C]?|S$+L%vRhSg^AlPqDg~');
define('LOGGED_IN_SALT',   'yM=TJi(XoON!FZIV|t(WSje-|{q6m#,#Bb@n-=+O=u@&<o))m8@PVbj0#Fv4f)qi');
define('NONCE_SALT',       ']ty?w++iP2?k~L=LfMBiHc0RoE *b+gS:}kSE r(DipY~q`WN_vVIhYhw3.4|[e}');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
if (!isset($table_prefix)) {
    $table_prefix  = 'hs_';
}

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'de_DE');

define('DISALLOW_FILE_EDIT', true);

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
if (!defined('WP_DEBUG')) {
  define('WP_DEBUG', false);
}

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
