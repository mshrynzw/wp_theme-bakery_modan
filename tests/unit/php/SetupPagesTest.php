<?php
/**
 * Tests for front page setup decisions.
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * 単体テスト用。WordPress の add_action は読み込まない。
	 *
	 * @param mixed $callback Callback.
	 */
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ): bool {
		if ( ! isset( $GLOBALS['bkery_unit_actions'] ) || ! is_array( $GLOBALS['bkery_unit_actions'] ) ) {
			$GLOBALS['bkery_unit_actions'] = array();
		}

		$GLOBALS['bkery_unit_actions'][] = array(
			'hook'          => $hook,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return true;
	}
}

require_once dirname( __DIR__, 3 ) . '/inc/setup-pages.php';

final class SetupPagesTest extends TestCase {
	public function test_front_page_definition_is_the_published_home_page(): void {
		self::assertSame(
			array(
				'post_title'   => 'ホーム',
				'post_name'    => 'home',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			bkery_front_page_definition()
		);
	}

	public function test_existing_page_is_reused_and_not_created_again(): void {
		self::assertSame(
			array(
				'action'  => 'use',
				'page_id' => 42,
			),
			bkery_front_page_plan( 42 )
		);
	}

	public function test_missing_page_is_marked_for_creation(): void {
		$expected = array(
			'action'  => 'create',
			'page_id' => 0,
		);

		self::assertSame( $expected, bkery_front_page_plan( 0 ) );
		self::assertSame( $expected, bkery_front_page_plan( -1 ) );
	}

	public function test_reading_options_use_the_page_and_omit_the_posts_page(): void {
		self::assertSame(
			array(
				'show_on_front' => 'page',
				'page_on_front' => 15,
			),
			bkery_front_page_reading_options( 15 )
		);
		self::assertArrayNotHasKey( 'page_for_posts', bkery_front_page_reading_options( 15 ) );
	}

	public function test_setup_is_hooked_to_theme_activation(): void {
		$matched = array();
		foreach ( $GLOBALS['bkery_unit_actions'] as $action ) {
			if ( 'after_switch_theme' === $action['hook'] && 'bkery_setup_front_page' === $action['callback'] ) {
				$matched[] = $action;
			}
		}

		self::assertCount( 1, $matched );
		self::assertSame( 10, $matched[0]['priority'] );
	}
}
