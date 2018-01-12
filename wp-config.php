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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'jua10013_wp66');

/** MySQL database username */
define('DB_USER', 'jua10013_wp66');

/** MySQL database password */
define('DB_PASSWORD', ']L[14T5kpS');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
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
define('AUTH_KEY',         'fbdaytvr3jkkasxo5xrjxdxnaj3ja7v4tcoz5bjqlc3hi07fct9ith2mdjhll3bf');
define('SECURE_AUTH_KEY',  'ykcqejd8okkjtpj1arr1qjukotrqnecxr3x3aclrez7rohki7wore0ios90ixqrw');
define('LOGGED_IN_KEY',    'ectb4kt2msqvratigkh7rnwklietcej8l7ei5oqmzsiumjyeq61qhrxn4pmywymc');
define('NONCE_KEY',        'vjydzokxpq2orlnkqi98bediayduavtpfuen5bdbnpdce5dvdxlwrokttqine7bn');
define('AUTH_SALT',        'a0f4pfmp2tfybawpbga4rpbceuvkxifuiyqq3td7mcmhdwjwianhqdwewpw3ktuz');
define('SECURE_AUTH_SALT', 'swo0c1x8vl9pyzyjr8eahniijmdbfgxymku9kt1z8khb815ktcj1q9p6v5ftkv30');
define('LOGGED_IN_SALT',   'rxkdzov0d5mjpsxt6voac7gjruusovgcgzqo0ucgrteskoqurjhiacssfogz1ym7');
define('NONCE_SALT',       'ugx6c1aitxpox27gxxljeermi90nrpcjih8ovdunml0nvevj4m1agku1rapiqzgz');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp0h_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
