<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ebdb' );

/** Database username */
define( 'DB_USER', 'aljuser' );

/** Database password */
define( 'DB_PASSWORD', 'U4CY1GvgHR2t' );

/** Database hostname */
define( 'DB_HOST', 'awseb-e-3i5pjtngg9-stack-awsebrdsdatabase-6bjvpisxwszr.cbckgsa4ua2e.eu-west-1.rds.amazonaws.com' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'MV#PA`E9MnVeiL-|qyhl1n%MO~e+C&tz&-%1[_HYWN`=(|=F[-.;U]c|.5yJRS2/');
define('SECURE_AUTH_KEY',  'M,e/*jOZ5xX$7|#8w#oXJUK7E/B73Cr~3T<!1,5b|)PU-vi5P|AT.H]+SjT5jDs/');
define('LOGGED_IN_KEY',    '`PFUeF0&DmwlQU8@Y1qb~@l0|w=:R7{zl&$i+Q}X.%Oc+}1uo#vzqm`6{qS+ZXp`');
define('NONCE_KEY',        'jTz=:.`d)N|vQ}~tI~ymI([}8|rIKKqY/=|rPyf.LLU^tL$<II-fDq2~zQ.(0yr2');
define('AUTH_SALT',        'U-QHX8<{Rb#io_dV$~U>&SU0XoS]$xgwfcT+~Cigvk%oD6L*5zJ);;E-?|+>u(<*');
define('SECURE_AUTH_SALT', '@Hl*O>*eGT.a_lJpb+%5Ua@>.xs+Mh3?.v7ZS$G%)}g|}eL-D@a9xSu70m6{<@}5');
define('LOGGED_IN_SALT',   'LTT/Y:0n0c2)-ud6F>@m!vL}3>):w;pmr2_r.11iCdPCl^hg8Y}W5{#KxK 2)MH}');
define('NONCE_SALT',       'h!pSa5r-FbdSd4NR:En4H,mH|gZ~.^+wFH5XqC9B9pFI2uh|mpNo5SCo^-PV;=St');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
