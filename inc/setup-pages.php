<?php
/**
 * 有効化時の固定ページと表示設定。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * フロントページにする固定ページの投稿データ。
 *
 * @return array{post_title: string, post_name: string, post_status: string, post_type: string, post_content: string}
 */
function bkery_front_page_definition(): array {
	return array(
		'post_title'   => 'ホーム',
		'post_name'    => 'home',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	);
}

/**
 * スラッグ home の固定ページがあれば再利用し、無ければ新規作成する。
 *
 * @param int $existing_page_id 既存ページ ID。0 以下は未作成。
 * @return array{action: string, page_id: int}
 */
function bkery_front_page_plan( int $existing_page_id ): array {
	if ( $existing_page_id > 0 ) {
		return array(
			'action'  => 'use',
			'page_id' => $existing_page_id,
		);
	}

	return array(
		'action'  => 'create',
		'page_id' => 0,
	);
}

/**
 * ホームページ表示のオプション。投稿ページは作らない。
 *
 * @param int $page_id フロントページにする固定ページ ID。
 * @return array{show_on_front: string, page_on_front: int}
 */
function bkery_front_page_reading_options( int $page_id ): array {
	return array(
		'show_on_front' => 'page',
		'page_on_front' => $page_id,
	);
}

/**
 * スラッグに一致する固定ページ ID。無ければ 0。
 */
function bkery_find_front_page_id( string $slug ): int {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! is_object( $page ) || ! isset( $page->ID, $page->post_type ) || 'page' !== $page->post_type ) {
		return 0;
	}

	return (int) $page->ID;
}

/**
 * スラッグ home の固定ページを用意し、サイトのフロントページにする。
 */
function bkery_setup_front_page(): void {
	$definition = bkery_front_page_definition();
	$plan       = bkery_front_page_plan( bkery_find_front_page_id( $definition['post_name'] ) );
	$page_id    = $plan['page_id'];

	if ( 'create' === $plan['action'] ) {
		$inserted = wp_insert_post( $definition, true );
		if ( is_wp_error( $inserted ) ) {
			return;
		}
		$page_id = (int) $inserted;
	}

	if ( $page_id <= 0 ) {
		return;
	}

	foreach ( bkery_front_page_reading_options( $page_id ) as $option => $value ) {
		update_option( $option, $value );
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'bkery_setup_front_page' );
