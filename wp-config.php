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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'materiel_wp_ozlim' );

/** MySQL database username */
define( 'DB_USER', 'materiel_wp_vcoli' );

/** MySQL database password */
define( 'DB_PASSWORD', '6m2Tj!GwvQ7B3PS*' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'gr5|mf3[_YoGQ!P[oeTx939yR9:Poq@TSU*!2Kim[z+#G)&92dB7dU6DXnX%;s(2');
define('SECURE_AUTH_KEY', 'SK*72E7L(E3[]B3v4(Gu9z2v5xTze6Mn[40uY+c%oGA3f!)G63W)CfhJa_alN1/@');
define('LOGGED_IN_KEY', 'R-S2p#w5dfx;Io@2(:7G003~]!5c+~_7)Tynev:N&K#0POx085VWK-t6DVu&/Dbl');
define('NONCE_KEY', 'J(-EXOg-y8dI5iop+xG5YN9Ix%5%k_Kc/Ac+sV60m1a#CWz2:Zfc@/Vy+p-n(;3)');
define('AUTH_SALT', '1e)d%m#;eiwEkQeCiZ-5b4C%L/(mi)95H;:2t5t0Q3dgk7]u[;45#5]yyUZ;VBBx');
define('SECURE_AUTH_SALT', '(9#a%%/5))qhoFcB752xtxuQ(2[(9z0A*8Ul_vIGDp-]v|~t2re-4B&0niuo860l');
define('LOGGED_IN_SALT', '1#EM~6%@S8YQJ+g59nLaEq&Bg5in8v9XMn6QX[[5@0(50j~4[M88i4:tn32AM-D2');
define('NONCE_SALT', 'mp:EUz1CG*r[n81M~%B/tl8i9_F|SS2HqbySj]8Y9e6-&0Tjk)5dv079tnHn~~CE');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = '3QGFlgA1_';

define( 'WP_HOME', 'https://dev.materiel.discount/' );
define( 'WP_SITEURL', 'https://dev.materiel.discount/' );

define('WP_ALLOW_MULTISITE', false);
/* That's all, stop editing! Happy publishing. */

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true);
define( 'WP_DEBUG_DISPLAY', false);
define( 'SCRIPT_DEBUG', true );

@ini_set( 'display_errors', 0 );



/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
