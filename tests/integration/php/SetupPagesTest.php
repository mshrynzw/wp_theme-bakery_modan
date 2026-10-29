<?php
/**
 * Integration tests for the front page setup.
 *
 * @package bakery-s
 */

/**
 * 固定ページ「ホーム」とフロントページ設定。
 */
class SetupPagesIntegrationTest extends WP_UnitTestCase {
	/**
	 * スラッグ home の固定ページを消す。
	 */
	private function delete_home_pages(): void {
		foreach ( $this->home_page_ids() as $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}

	/**
	 * @return array<int, int>
	 */
	private function home_page_ids(): array {
		$ids = get_posts(
			array(
				'post_type'              => 'page',
				'name'                   => 'home',
				'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'nopaging'               => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return array_map( 'intval', $ids );
	}

	public function test_activation_creates_home_and_sets_reading_options(): void {
		$this->delete_home_pages();
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', 0 );
		$posts_page = get_option( 'page_for_posts' );
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		delete_option( 'rewrite_rules' );

		bkery_setup_front_page();

		$page = get_page_by_path( 'home', OBJECT, 'page' );
		$this->assertInstanceOf( WP_Post::class, $page );
		$this->assertSame( 'ホーム', $page->post_title );
		$this->assertSame( 'home', $page->post_name );
		$this->assertSame( 'page', $page->post_type );
		$this->assertSame( 'publish', $page->post_status );
		$this->assertSame( 'page', get_option( 'show_on_front' ) );
		$this->assertSame( (int) $page->ID, (int) get_option( 'page_on_front' ) );
		$this->assertSame( $posts_page, get_option( 'page_for_posts' ) );
		$this->assertNotEmpty( get_option( 'rewrite_rules' ) );
		$this->assertIsArray( get_option( 'rewrite_rules' ) );
		$this->assertSame( array( (int) $page->ID ), $this->home_page_ids() );
	}

	public function test_existing_home_page_is_reused(): void {
		$this->delete_home_pages();
		$existing_id = $this->factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => '既存のホーム',
				'post_name'    => 'home',
				'post_status'  => 'publish',
				'post_content' => 'そのまま残す',
			)
		);
		update_option( 'show_on_front', 'posts' );
		update_option( 'page_on_front', 0 );

		bkery_setup_front_page();
		bkery_setup_front_page();

		$this->assertSame( 'page', get_option( 'show_on_front' ) );
		$this->assertSame( (int) $existing_id, (int) get_option( 'page_on_front' ) );
		$this->assertSame( array( (int) $existing_id ), $this->home_page_ids() );

		$page = get_post( $existing_id );
		$this->assertInstanceOf( WP_Post::class, $page );
		$this->assertSame( '既存のホーム', $page->post_title );
		$this->assertSame( 'そのまま残す', $page->post_content );
	}

	public function test_existing_draft_home_is_reused_without_publishing(): void {
		$this->delete_home_pages();
		$draft_id = $this->factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => '下書きホーム',
				'post_name'   => 'home',
				'post_status' => 'draft',
			)
		);

		bkery_setup_front_page();

		$this->assertSame( (int) $draft_id, (int) get_option( 'page_on_front' ) );
		$this->assertSame( 'draft', get_post_status( $draft_id ) );
		$this->assertSame( array( (int) $draft_id ), $this->home_page_ids() );
	}

	public function test_hook_is_attached_to_theme_switch(): void {
		$this->assertSame( 10, has_action( 'after_switch_theme', 'bkery_setup_front_page' ) );
	}
}
