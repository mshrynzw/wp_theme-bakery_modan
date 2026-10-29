<?php
/**
 * パンの取得、焼き上がり集約、フロントデータ。
 *
 * @package bakery-s
 */

class BreadQueryTest extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		bkery_register_bread();
		bkery_register_bread_meta();
	}

	public function tear_down() {
		wp_set_current_user( 0 );
		foreach (
			array(
				'bkery_shop_closed_weekdays',
				'bkery_scene_hero_image_1',
				'bkery_hero_slide_1',
				'bkery_video_id',
				'bkery_video_poster_id',
			) as $key
		) {
			remove_theme_mod( $key );
		}
		parent::tear_down();
	}

	/**
	 * @param array<string, mixed> $args 投稿引数。
	 * @param array<string, mixed> $meta メタ。
	 */
	private function create_bread( array $args, array $meta = array() ): int {
		$post_id = self::factory()->post->create(
			array_merge(
				array(
					'post_type'   => 'bread',
					'post_status' => 'publish',
				),
				$args
			)
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return (int) $post_id;
	}

	public function test_lines_keep_menu_order_and_treat_missing_meta_as_regular(): void {
		$this->create_bread(
			array(
				'post_name'  => 'season-croque',
				'menu_order' => 10,
			),
			array(
				'bkery_bread_line'       => 'seasonal',
				'bkery_bread_bake_times' => '10:00',
			)
		);
		$this->create_bread(
			array(
				'post_name'  => 'croissant',
				'menu_order' => 20,
			),
			array(
				'bkery_bread_line'       => 'regular',
				'bkery_bread_bake_times' => '10:00,15:00',
			)
		);
		$plain_id = $this->create_bread(
			array(
				'post_name'    => 'plain-bread',
				'menu_order'   => 30,
				'post_excerpt' => '',
				'post_content' => '<p>最初の段落</p><p>次</p>',
			),
			array(
				'bkery_bread_bake_times' => '09:00',
			)
		);
		$this->create_bread(
			array(
				'post_name'   => 'draft-bread',
				'post_status' => 'draft',
				'menu_order'  => 1,
			),
			array(
				'bkery_bread_line'       => 'seasonal',
				'bkery_bread_bake_times' => '08:00',
			)
		);
		$same_order_first  = $this->create_bread(
			array(
				'post_name'  => 'same-a',
				'menu_order' => 40,
			),
			array(
				'bkery_bread_line' => 'regular',
			)
		);
		$same_order_second = $this->create_bread(
			array(
				'post_name'  => 'same-b',
				'menu_order' => 40,
			),
			array(
				'bkery_bread_line' => 'regular',
			)
		);

		$seasonal_names = wp_list_pluck( bkery_get_breads_by_line( 'seasonal' ), 'post_name' );
		$regular_names  = wp_list_pluck( bkery_get_breads_by_line( 'regular' ), 'post_name' );

		self::assertSame( array( 'season-croque' ), $seasonal_names );
		self::assertSame( array( 'croissant', 'plain-bread', 'same-a', 'same-b' ), $regular_names );
		self::assertTrue( $same_order_first < $same_order_second );
		self::assertSame( '最初の段落', bkery_bread_card_excerpt( $plain_id ) );

		self::assertSame(
			array(
				array(
					'time'  => '09:00',
					'items' => array( 'plain-bread' ),
				),
				array(
					'time'  => '10:00',
					'items' => array( 'season-croque', 'croissant' ),
				),
				array(
					'time'  => '15:00',
					'items' => array( 'croissant' ),
				),
			),
			bkery_get_bake_schedule()
		);
	}

	public function test_frontend_data_includes_shop_schedule_and_scene_images(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$file     = DIR_TESTDATA . '/images/test-image.jpg';
		$scene_id = self::factory()->attachment->create_upload_object( $file );
		$slide_id = self::factory()->attachment->create_upload_object( $file );
		self::assertIsInt( $scene_id );
		self::assertIsInt( $slide_id );

		$this->create_bread(
			array(
				'post_name'  => 'shokupan',
				'menu_order' => 5,
			),
			array(
				'bkery_bread_line'       => 'regular',
				'bkery_bread_bake_times' => '10:00',
			)
		);
		set_theme_mod( 'bkery_shop_closed_weekdays', '3, 2, 3' );
		set_theme_mod( 'bkery_scene_hero_image_1', $scene_id );
		set_theme_mod( 'bkery_hero_slide_1', $slide_id );
		set_theme_mod( 'bkery_video_id', $slide_id );
		set_theme_mod( 'bkery_video_poster_id', $scene_id );

		$data      = bkery_get_frontend_data();
		$scene_url = wp_get_attachment_image_url( $scene_id, 'full' );
		$slide_url = wp_get_attachment_image_url( $slide_id, 'full' );
		$file_url  = wp_get_attachment_url( $slide_id );

		self::assertSame( array( 2, 3 ), $data['shop']['closedWeekdays'] );
		self::assertSame(
			array(
				array(
					'time'  => '10:00',
					'items' => array( 'shokupan' ),
				),
			),
			$data['bakeSchedule']
		);
		self::assertSame( array( $scene_url ), $data['scenes']['hero']['image'] );
		self::assertSame( $file_url, $data['media']['video'] );
		self::assertSame( $scene_url, $data['media']['videoPoster'] );

		remove_theme_mod( 'bkery_scene_hero_image_1' );
		$fallback = bkery_get_frontend_data();
		self::assertSame( array( $slide_url ), $fallback['scenes']['hero']['image'] );
		self::assertSame( array( $slide_url ), $fallback['scenes']['bake']['image'] );
		self::assertSame( array(), $fallback['scenes']['season']['image'] );
	}
}
