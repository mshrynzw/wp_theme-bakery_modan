<?php
/**
 * WordPress integration test configuration.
 *
 * @package bakery-s
 */

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'local_tests' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: 'root' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1:10000' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = getenv( 'WP_TESTS_TABLE_PREFIX' ) ?: 'wptests_';

define( 'WP_TESTS_DOMAIN', getenv( 'WP_TESTS_DOMAIN' ) ?: 'example.test' );
define( 'WP_TESTS_EMAIL', getenv( 'WP_TESTS_EMAIL' ) ?: 'admin@example.test' );
define( 'WP_TESTS_TITLE', getenv( 'WP_TESTS_TITLE' ) ?: 'Bakery S Tests' );
define( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: PHP_BINARY . ' -d extension=mysqli' );

$theme_dir = dirname( __DIR__, 2 );
$wp_root   = realpath( $theme_dir . '/../../..' );

if ( false === $wp_root ) {
	throw new RuntimeException( 'Unable to resolve WordPress root for integration tests. Place the theme under wp-content/themes/.' );
}

define( 'ABSPATH', $wp_root . DIRECTORY_SEPARATOR );

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
