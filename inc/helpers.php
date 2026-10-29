<?php
/**
 * テーマ設定の取得。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * 設定の初期値。未登録のキーは空文字。
 *
 * @return string|int
 */
function bkery_default( string $key ) {
	$registry = bkery_theme_mod_registry();
	if ( ! isset( $registry[ $key ] ) ) {
		return '';
	}

	return $registry[ $key ]['default'];
}

/**
 * テーマ設定を初期値つきで返す。画像 ID の初期値は整数のまま返す。
 *
 * @return mixed
 */
function bkery_get_theme_mod( string $key ) {
	$default = bkery_default( $key );
	if ( ! function_exists( 'get_theme_mod' ) ) {
		return $default;
	}

	$value = get_theme_mod( $key, $default );
	if ( is_int( $default ) ) {
		return absint( $value );
	}
	if ( null === $value || false === $value ) {
		return $default;
	}

	return $value;
}
