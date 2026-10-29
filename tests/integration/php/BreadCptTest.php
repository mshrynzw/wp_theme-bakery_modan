<?php
/**
 * CPT bread とタクソノミー、メタ登録。
 *
 * @package bakery-s
 */

class BreadCptTest extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		bkery_register_bread();
		bkery_register_bread_meta();
	}

	public function tear_down() {
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function test_bread_post_type_is_registered(): void {
		$post_type = get_post_type_object( 'bread' );

		self::assertNotNull( $post_type );
		self::assertTrue( $post_type->public );
		self::assertFalse( $post_type->has_archive );
		self::assertTrue( $post_type->show_in_rest );
		self::assertSame( 'dashicons-carrot', $post_type->menu_icon );
		self::assertIsArray( $post_type->rewrite );
		self::assertSame( 'bread', $post_type->rewrite['slug'] );
		self::assertFalse( $post_type->rewrite['with_front'] );
		foreach ( array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ) as $feature ) {
			self::assertTrue( post_type_supports( 'bread', $feature ) );
		}
	}

	public function test_bread_category_taxonomy_is_registered(): void {
		$taxonomy = get_taxonomy( 'bread_category' );

		self::assertNotFalse( $taxonomy );
		self::assertTrue( $taxonomy->hierarchical );
		self::assertTrue( $taxonomy->show_admin_column );
		self::assertTrue( $taxonomy->show_in_rest );
		self::assertFalse( $taxonomy->rewrite );
		self::assertContains( 'bread', $taxonomy->object_type );
	}

	public function test_bread_meta_is_registered_for_rest(): void {
		$keys = get_registered_meta_keys( 'post', 'bread' );

		self::assertSame( 'bkery_sanitize_bread_line', $keys['bkery_bread_line']['sanitize_callback'] );
		self::assertSame( 'absint', $keys['bkery_bread_price']['sanitize_callback'] );
		self::assertSame( 'bkery_sanitize_bake_times', $keys['bkery_bread_bake_times']['sanitize_callback'] );
		self::assertTrue( $keys['bkery_bread_line']['show_in_rest'] );

		$auth = $keys['bkery_bread_line']['auth_callback'];
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		self::assertFalse( $auth() );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		self::assertTrue( $auth() );

		$term_keys = get_registered_meta_keys( 'term', 'bread_category' );
		self::assertSame( 'absint', $term_keys['bkery_bread_category_order']['sanitize_callback'] );
		$term_auth = $term_keys['bkery_bread_category_order']['auth_callback'];
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		self::assertFalse( $term_auth() );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		self::assertTrue( $term_auth() );
	}

	public function test_hooks_and_admin_columns_are_registered(): void {
		self::assertSame( 0, has_action( 'init', 'bkery_register_bread' ) );
		self::assertSame( 10, has_action( 'init', 'bkery_register_bread_meta' ) );
		self::assertNotFalse( has_action( 'after_switch_theme', 'bkery_seed_bread_categories' ) );
		self::assertNotFalse( has_action( 'add_meta_boxes', 'bkery_add_bread_metabox' ) );
		self::assertNotFalse( has_action( 'save_post_bread', 'bkery_save_bread_meta' ) );

		$columns = bkery_bread_admin_columns(
			array(
				'cb'    => '<input />',
				'title' => 'タイトル',
				'date'  => '日付',
			)
		);
		self::assertSame(
			array( 'cb', 'title', 'bkery_bread_line', 'bkery_bread_price', 'date' ),
			array_keys( $columns )
		);

		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'bread',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, 'bkery_bread_line', 'seasonal' );
		update_post_meta( $post_id, 'bkery_bread_price', 480 );

		ob_start();
		bkery_render_bread_admin_column( 'bkery_bread_line', $post_id );
		$line = ob_get_clean();
		ob_start();
		bkery_render_bread_admin_column( 'bkery_bread_price', $post_id );
		$price = ob_get_clean();

		self::assertSame( '季節限定', $line );
		self::assertSame( '480', $price );
	}
}
