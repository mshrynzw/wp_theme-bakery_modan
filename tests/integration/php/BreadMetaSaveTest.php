<?php
/**
 * パンメタの保存、nonce、権限。
 *
 * @package bakery-s
 */

class BreadMetaSaveTest extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		bkery_register_bread();
		bkery_register_bread_meta();
	}

	public function tear_down() {
		$_POST = array();
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function test_saves_meta_with_valid_nonce_and_sanitizes_bake_times(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'bread',
				'post_status' => 'publish',
			)
		);

		$_POST['bkery_bread_meta_nonce']  = wp_create_nonce( 'bkery_bread_meta' );
		$_POST['bkery_bread_line']        = ' seasonal ';
		$_POST['bkery_bread_price']       = '12abc';
		$_POST['bkery_bread_bake_times']  = "11:00\n10:00、11:00 25:00";

		bkery_save_bread_meta( $post_id );

		self::assertSame( 'seasonal', (string) get_post_meta( $post_id, 'bkery_bread_line', true ) );
		self::assertSame( 12, absint( get_post_meta( $post_id, 'bkery_bread_price', true ) ) );
		self::assertSame( '11:00,10:00', (string) get_post_meta( $post_id, 'bkery_bread_bake_times', true ) );
	}

	public function test_registered_meta_sanitizes_direct_updates(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'bread',
				'post_status' => 'publish',
			)
		);

		update_post_meta( $post_id, 'bkery_bread_line', 'limited' );
		update_post_meta( $post_id, 'bkery_bread_price', -15 );
		update_post_meta( $post_id, 'bkery_bread_bake_times', '11:00,25:00,11:00' );

		self::assertSame( 'regular', (string) get_post_meta( $post_id, 'bkery_bread_line', true ) );
		self::assertSame( 15, absint( get_post_meta( $post_id, 'bkery_bread_price', true ) ) );
		self::assertSame( '11:00', (string) get_post_meta( $post_id, 'bkery_bread_bake_times', true ) );
	}

	public function test_does_not_save_without_valid_nonce(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'bread',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, 'bkery_bread_line', 'seasonal' );
		update_post_meta( $post_id, 'bkery_bread_bake_times', '10:00' );

		$_POST['bkery_bread_meta_nonce'] = 'invalid';
		$_POST['bkery_bread_line']       = 'regular';
		$_POST['bkery_bread_bake_times'] = '12:00';

		bkery_save_bread_meta( $post_id );

		self::assertSame( 'seasonal', (string) get_post_meta( $post_id, 'bkery_bread_line', true ) );
		self::assertSame( '10:00', (string) get_post_meta( $post_id, 'bkery_bread_bake_times', true ) );
	}

	public function test_does_not_save_without_edit_capability(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'bread',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, 'bkery_bread_line', 'seasonal' );

		$_POST['bkery_bread_meta_nonce'] = wp_create_nonce( 'bkery_bread_meta' );
		$_POST['bkery_bread_line']       = 'regular';
		$_POST['bkery_bread_price']      = '980';

		bkery_save_bread_meta( $post_id );

		self::assertSame( 'seasonal', (string) get_post_meta( $post_id, 'bkery_bread_line', true ) );
		self::assertSame( 0, absint( get_post_meta( $post_id, 'bkery_bread_price', true ) ) );
	}

	public function test_ignores_posts_that_are_not_bread(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$_POST['bkery_bread_meta_nonce'] = wp_create_nonce( 'bkery_bread_meta' );
		$_POST['bkery_bread_line']       = 'seasonal';

		bkery_save_bread_meta( $post_id );

		self::assertSame( '', (string) get_post_meta( $post_id, 'bkery_bread_line', true ) );
	}
}
