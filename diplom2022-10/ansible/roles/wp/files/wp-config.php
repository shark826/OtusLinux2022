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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpres' );

/** Database username */
define( 'DB_USER', 'wpuser' );

/** Database password */
define( 'DB_PASSWORD', 'Pa!!1234' );

/** Database hostname */
define( 'DB_HOST', '10.0.0.20' );

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
define( 'AUTH_KEY',         '%BH06R:gzXr0GdMe8>WjjOj!_eq=Ijd-CL5Ixak!Vu% {_|xA}wiypP A<y9KjQs' );
define( 'SECURE_AUTH_KEY',  'e.M nVc31@# [)_ua7*4KFw(:6iA#-j*Kp7azoF].+]Dl|&hZ=I-7a!v<1<r2@u!' );
define( 'LOGGED_IN_KEY',    'i!p7MkoK*rmT,xlF!>lT>L0?>&m6-4b~qfh)mXa%hdR(}D|${(k}LXjT]~**u+#<' );
define( 'NONCE_KEY',        'Q5P]B%Q6`AU3$b,U&B0p#aw2/`R4TXg{y3xNlX?,C/x?8H*[/UFpw]XN*l2C/3S.' );
define( 'AUTH_SALT',        '88e/6, !`Da}hw)gD+=WLum* c<4nl5<)mEdHmTsDn$Yt|QYr@kUCI&~qM@7V06!' );
define( 'SECURE_AUTH_SALT', 'KwHj@Uv*zG[w>%zkh>N4>0jU4{7bp,Lt>>Y</1/;R9QOCn1#u3G^KC$}(Yl$0cU:' );
define( 'LOGGED_IN_SALT',   '}C6yh<f&R2g/x)|Ew2s+*xRi![ )= 0y$&pbX_BTLV%M(+)d0x5KH4?A;70Mk;J4' );
define( 'NONCE_SALT',       '=zXiu6;3Kf*Yzah<$B^ibRhOE]Xd/@{1}{xV(0O6a_?5ZKTY6n**4>Q!lJ,X(/bU' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
