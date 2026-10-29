<?php
/**
 * CSS / JS / Google Fonts の読み込み。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outfit と Zen Kaku Gothic New を登録する。
 */
function bkery_register_google_fonts() {
	$font_url = 'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap';
	wp_register_style( 'bkery-google-fonts', $font_url, array(), null );
}
add_action( 'wp_enqueue_scripts', 'bkery_register_google_fonts', 5 );

/**
 * Google Fonts に preconnect を入れる。
 *
 * @param array<int, mixed> $urls          リソース URL。
 * @param string            $relation_type 関係タイプ。
 * @return array<int, mixed>
 */
function bkery_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type ) {
		return $urls;
	}
	if ( ! wp_style_is( 'bkery-google-fonts', 'enqueued' ) ) {
		return $urls;
	}
	$urls[] = array(
		'href' => 'https://fonts.googleapis.com',
	);
	$urls[] = array(
		'href'        => 'https://fonts.gstatic.com',
		'crossorigin' => 'anonymous',
	);
	return $urls;
}
add_filter( 'wp_resource_hints', 'bkery_resource_hints', 10, 2 );

/**
 * Vite の成果物と、フロントが使う bkeryTheme を読み込む。
 */
function bkery_enqueue_assets() {
	$css_path = get_theme_file_path( '/dist/css/app.css' );
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'bkery-app',
			get_theme_file_uri( '/dist/css/app.css' ),
			array( 'bkery-google-fonts' ),
			(string) filemtime( $css_path )
		);
	} else {
		wp_enqueue_style( 'bkery-google-fonts' );
	}

	if ( wp_style_is( 'bkery-app', 'enqueued' ) && function_exists( 'bkery_get_color_palette_css' ) ) {
		$color_css = bkery_get_color_palette_css();
		if ( '' !== $color_css ) {
			wp_add_inline_style( 'bkery-app', $color_css );
		}
	}

	$js_path = get_theme_file_path( '/dist/js/theme.js' );
	if ( ! file_exists( $js_path ) ) {
		return;
	}

	wp_enqueue_script(
		'bkery-main',
		get_theme_file_uri( '/dist/js/theme.js' ),
		array(),
		(string) filemtime( $js_path ),
		true
	);

	if ( function_exists( 'bkery_get_frontend_data' ) ) {
		wp_localize_script( 'bkery-main', 'bkeryTheme', bkery_get_frontend_data() );
	}
}
add_action( 'wp_enqueue_scripts', 'bkery_enqueue_assets', 20 );

/**
 * Vite の出力に type="module" を付ける。
 *
 * @param string $tag    スクリプトタグ。
 * @param string $handle ハンドル名。
 * @return string
 */
function bkery_script_loader_tag( $tag, $handle ) {
	if ( 'bkery-main' !== $handle ) {
		return $tag;
	}
	if ( false !== strpos( $tag, ' type=' ) ) {
		$replaced = preg_replace( '/ type=(["\']).*?\1/', ' type="module"', $tag );
		return is_string( $replaced ) ? $replaced : $tag;
	}
	return str_replace( '<script ', '<script type="module" ', $tag );
}
add_filter( 'script_loader_tag', 'bkery_script_loader_tag', 10, 2 );
