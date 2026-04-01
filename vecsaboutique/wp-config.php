<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'vecsaboutique_wp_gzhtn' );

/** Database username */
define( 'DB_USER', 'vecsaboutique_wp_sqdqr' );

/** Database password */
define( 'DB_PASSWORD', 'E0yHMJd!B8AsZ56~' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('FS_METHOD', 'direct');
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
define('AUTH_KEY', 'xm!C5|:_[53i&&g3K7A2UU#H]8j;b~Uo7J/y&(5dVm!m7xr8/W;9tB!hbD*9C48)');
define('SECURE_AUTH_KEY', ':y!O5#Lh7r]B1[49x0O2;D~p(@195LQ]1!m86QT;2c1E_::X88zCk:5B#8)&5|!9');
define('LOGGED_IN_KEY', '_j3l/13bAV%SkuVd90&+ODR4(nY1SvGn_k2s-f*77mB-oB42_sg|J:/b8s:_qaEg');
define('NONCE_KEY', 'cmaR-Y7f-1klzhk%FJ92HEbd#7PbbHcD5)e[b(iIR2S85g74[9IF_s|lGP[80f)6');
define('AUTH_SALT', '%42M0+h(98UU-R40D*2r7*BhR;S9F3;EU9+j&78IFE|1e(/q)c)L&~9zvQ72Eb5z');
define('SECURE_AUTH_SALT', 'eL[r|+c4/Ia%eq&5m(vV]Tp~Dc~+BN#%#L+cWl|eV11q#_t@x1xAa|RO~u)9+ru8');
define('LOGGED_IN_SALT', '~:q|K2RR+i9U6~cHLdD)#iWaUyH4QhU-5/U~_qS%b&o09;7&-HE7j2(8&+~n5f]y');
define('NONCE_SALT', '80:(O3D*ANB7j0Co9:r63f-Kj*u6w+8_F&7C4jv+I@cnS+WUFr3]9yN:]zplI[Ia');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'yiQpVMzl_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
// Asegúrate de que estas también estén así o bórralas:
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

define( 'DISALLOW_FILE_EDIT', true );
define( 'CONCATENATE_SCRIPTS', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
