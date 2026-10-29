<?php
/**
 * パン分類の初期投入。
 *
 * @package bakery-s
 */

class BreadSeedTest extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		bkery_register_bread();
		bkery_register_bread_meta();
	}

	/**
	 * テスト用に初期分類と投入済みフラグを消す。
	 */
	private function delete_seeded_categories(): void {
		delete_option( 'bkery_bread_categories_seeded' );
		foreach ( array( 'meal', 'sweet', 'drink' ) as $slug ) {
			$existing = term_exists( $slug, 'bread_category' );
			if ( is_array( $existing ) && isset( $existing['term_id'] ) ) {
				wp_delete_term( (int) $existing['term_id'], 'bread_category' );
			}
		}
	}

	public function test_seed_creates_categories_once_and_skips_demo_products(): void {
		$this->delete_seeded_categories();

		bkery_seed_bread_categories();

		$terms = get_terms(
			array(
				'taxonomy'   => 'bread_category',
				'hide_empty' => false,
				'slug'       => array( 'meal', 'sweet', 'drink' ),
			)
		);
		self::assertIsArray( $terms );
		self::assertCount( 3, $terms );
		self::assertSame( '1', get_option( 'bkery_bread_categories_seeded' ) );

		$orders = array(
			'meal'  => 10,
			'sweet' => 20,
			'drink' => 30,
		);
		foreach ( $orders as $slug => $order ) {
			$term = get_term_by( 'slug', $slug, 'bread_category' );
			self::assertInstanceOf( WP_Term::class, $term );
			self::assertSame( $order, (int) get_term_meta( $term->term_id, 'bkery_bread_category_order', true ) );
		}

		$breads_before = (int) wp_count_posts( 'bread' )->publish;
		bkery_seed_bread_categories();
		$terms_after = get_terms(
			array(
				'taxonomy'   => 'bread_category',
				'hide_empty' => false,
				'slug'       => array( 'meal', 'sweet', 'drink' ),
			)
		);
		self::assertIsArray( $terms_after );
		self::assertCount( 3, $terms_after );
		self::assertSame( $breads_before, (int) wp_count_posts( 'bread' )->publish );
	}

	public function test_categories_are_ordered_by_term_meta(): void {
		$this->delete_seeded_categories();
		bkery_seed_bread_categories();

		$slugs     = wp_list_pluck( bkery_get_bread_categories(), 'slug' );
		$positions = array();
		foreach ( array( 'meal', 'sweet', 'drink' ) as $slug ) {
			$positions[] = array_search( $slug, $slugs, true );
		}

		self::assertNotFalse( $positions[0] );
		self::assertTrue( $positions[0] < $positions[1] );
		self::assertTrue( $positions[1] < $positions[2] );
	}
}
