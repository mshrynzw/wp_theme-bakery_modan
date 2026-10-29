<?php
/**
 * Bootstrap for WordPress integration tests.
 *
 * @package bakery-s
 */

declare(strict_types=1);

$theme_dir = dirname( __DIR__, 2 );

require_once $theme_dir . '/vendor/autoload.php';

/**
 * Load tests/integration/.env.
 *
 * @param string $path .env path.
 */
function bkery_tests_load_env_file( string $path ): void {
	if ( ! is_readable( $path ) ) {
		return;
	}

	$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	if ( false === $lines ) {
		return;
	}

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line || str_starts_with( $line, '#' ) || ! str_contains( $line, '=' ) ) {
			continue;
		}

		[ $key, $value ] = array_map( 'trim', explode( '=', $line, 2 ) );
		if ( '' === $key || false !== getenv( $key ) ) {
			continue;
		}

		$value = trim( $value, "\"'" );
		putenv( $key . '=' . $value );
		$_ENV[ $key ]    = $value;
		$_SERVER[ $key ] = $value;
	}
}

/**
 * @param string $db_host DB_HOST string.
 * @return array{host:string, port:int|null}
 */
function bkery_tests_parse_db_host( string $db_host ): array {
	if ( preg_match( '/^(.+):([0-9]+)$/', $db_host, $matches ) ) {
		return array(
			'host' => $matches[1],
			'port' => (int) $matches[2],
		);
	}

	return array(
		'host' => $db_host,
		'port' => null,
	);
}

/**
 * Ensure the dedicated test database exists.
 */
function bkery_tests_ensure_database_exists(): void {
	if ( ! function_exists( 'mysqli_connect' ) ) {
		throw new RuntimeException( 'The mysqli extension is required for integration tests.' );
	}

	$db_name = getenv( 'WP_TESTS_DB_NAME' ) ?: 'local_tests';
	$db_user = getenv( 'WP_TESTS_DB_USER' ) ?: 'root';
	$db_pass = getenv( 'WP_TESTS_DB_PASSWORD' ) ?: 'root';
	$db_host = getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1:10000';
	$parsed  = bkery_tests_parse_db_host( $db_host );

	mysqli_report( MYSQLI_REPORT_OFF );
	$connection = mysqli_connect(
		$parsed['host'],
		$db_user,
		$db_pass,
		'',
		$parsed['port'] ?? (int) ini_get( 'mysqli.default_port' )
	);

	if ( ! $connection ) {
		throw new RuntimeException(
			sprintf(
				'Unable to connect to test MySQL at %s as %s: %s',
				$db_host,
				$db_user,
				mysqli_connect_error()
			)
		);
	}

	$escaped_db_name = str_replace( '`', '``', $db_name );
	$created         = mysqli_query(
		$connection,
		"CREATE DATABASE IF NOT EXISTS `{$escaped_db_name}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"
	);
	if ( ! $created ) {
		$error = mysqli_error( $connection );
		mysqli_close( $connection );
		throw new RuntimeException( 'Unable to create test database: ' . $error );
	}

	mysqli_close( $connection );
}

bkery_tests_load_env_file( __DIR__ . '/.env' );
bkery_tests_ensure_database_exists();

$config_file = __DIR__ . '/wp-tests-config.php';
defined( 'WP_TESTS_CONFIG_FILE_PATH' ) || define( 'WP_TESTS_CONFIG_FILE_PATH', $config_file );
putenv( 'WP_TESTS_CONFIG_FILE_PATH=' . $config_file );
$_ENV['WP_TESTS_CONFIG_FILE_PATH']    = $config_file;
$_SERVER['WP_TESTS_CONFIG_FILE_PATH'] = $config_file;

$wp_phpunit_dir = $theme_dir . '/vendor/wp-phpunit/wp-phpunit';

require_once $wp_phpunit_dir . '/includes/functions.php';

/**
 * Load this theme during the test suite.
 */
function bkery_tests_manually_load_theme(): void {
	call_user_func( 'switch_theme', basename( dirname( __DIR__, 2 ) ) );
}
tests_add_filter( 'muplugins_loaded', 'bkery_tests_manually_load_theme' );

require $wp_phpunit_dir . '/includes/bootstrap.php';
