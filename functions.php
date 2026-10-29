<?php
/**
 * テーマ初期化エントリ。
 *
 * 読み込み順は固定。新しい機能ファイルを足すときは、この並びと
 * docs/design/06_functions.md を同じ変更で更新する。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( '/inc/shop-hours.php' );
require_once get_theme_file_path( '/inc/theme-mods.php' );
require_once get_theme_file_path( '/inc/helpers.php' );
require_once get_theme_file_path( '/inc/colors.php' );
require_once get_theme_file_path( '/inc/enqueue.php' );
require_once get_theme_file_path( '/inc/customizer.php' );
require_once get_theme_file_path( '/inc/bread.php' );
require_once get_theme_file_path( '/inc/setup-pages.php' );

/**
 * テーマ機能の有効化。
 */
function bkery_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 160,
			'width'       => 480,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	load_theme_textdomain( 'bakery-s', get_theme_file_path( '/languages' ) );

	add_image_size( 'bkery-hero', 1400, 1750, false );
	add_image_size( 'bkery-card', 1200, 900, false );
	add_image_size( 'bkery-gallery', 1200, 1500, false );
	add_image_size( 'bkery-owner', 800, 840, false );
}
add_action( 'after_setup_theme', 'bkery_setup' );
