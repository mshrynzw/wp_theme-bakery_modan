<?php
/**
 * WordPress を使わないパン CPT の純粋ロジック。
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

$bkery_theme_dir = dirname(__DIR__, 3);
require_once $bkery_theme_dir . '/inc/helpers.php';
require_once $bkery_theme_dir . '/inc/bread.php';

final class BreadTest extends TestCase {
	public function test_sanitize_bread_line_accepts_only_seasonal(): void {
		self::assertSame('seasonal', bkery_sanitize_bread_line(' seasonal '));
		self::assertSame('regular', bkery_sanitize_bread_line('regular'));
		self::assertSame('regular', bkery_sanitize_bread_line('Seasonal'));
		self::assertSame('regular', bkery_sanitize_bread_line(''));
	}

	public function test_default_categories_match_the_design(): void {
		self::assertSame(
			array(
				array(
					'name'  => '食事パン',
					'slug'  => 'meal',
					'order' => 10,
				),
				array(
					'name'  => '甘いパン',
					'slug'  => 'sweet',
					'order' => 20,
				),
				array(
					'name'  => 'ドリンク',
					'slug'  => 'drink',
					'order' => 30,
				),
			),
			bkery_default_bread_categories()
		);
	}

	public function test_card_excerpt_prefers_excerpt_then_first_paragraph(): void {
		self::assertSame('短い抜粋', bkery_bread_card_excerpt_text('  短い抜粋  ', '<p>本文</p>'));
		self::assertSame('最初の段落', bkery_bread_card_excerpt_text('   ', "<p></p><p>最初の段落</p><p>次</p>"));
		self::assertSame('一段落', bkery_bread_card_excerpt_text('', "一段落\n\n二段落"));
		self::assertSame('太字を除く', bkery_bread_card_excerpt_text('', '<p>太字を<strong>除く</strong></p>'));
		self::assertSame('A & B', bkery_bread_card_excerpt_text('', '<p>A &amp; B</p>'));
		self::assertSame('', bkery_bread_card_excerpt_text('', '<script>alert(1)</script>'));
	}

	public function test_pick_scene_image_ids_uses_own_images_before_fallbacks(): void {
		self::assertSame(
			array(4, 7, 8),
			bkery_pick_scene_image_ids(array(0, 4, 7, 8), array(array(1)), 3)
		);
		self::assertSame(
			array(9, 10),
			bkery_pick_scene_image_ids(
				array(0, 0),
				array(
					'not-a-group',
					array(0),
					array(9, '10', -1, 11),
				),
				2
			)
		);
		self::assertSame(array(), bkery_pick_scene_image_ids(array(0), array(array(0)), 3));
		self::assertSame(array(), bkery_pick_scene_image_ids(array(4), array(), 0));
	}

	public function test_frontend_data_shape_uses_theme_defaults_without_wordpress(): void {
		$data = bkery_get_frontend_data();

		self::assertSame(array('shop', 'links', 'bakeSchedule', 'media', 'scenes', 'steam'), array_keys($data));
		self::assertSame(
			array(
				'open'           => '10:00',
				'close'          => '18:00',
				'cafeClose'      => '17:00',
				'closedWeekdays' => array(),
				'addressText'    => '〒123-4567 北海道札幌市○○区△△町1-2-3 1F',
			),
			$data['shop']
		);
		self::assertSame(
			array(
				'instagram'       => 'https://www.instagram.com/',
				'instagramHandle' => '@bakery_s（仮）',
				'map'             => bkery_default('bkery_shop_map_url'),
				'calendar'        => 'https://calendar.google.com/',
			),
			$data['links']
		);
		self::assertSame(array(), $data['bakeSchedule']);
		self::assertSame(
			array(
				'video'       => '',
				'videoPoster' => '',
			),
			$data['media']
		);
		self::assertSame(
			array(
				'enabled'    => 1,
				'density'    => 50,
				'speed'      => 50,
				'column'     => 100,
				'background' => 100,
			),
			$data['steam']
		);
		self::assertSame(bkery_scene_ids(), array_keys($data['scenes']));
		foreach (bkery_scene_ids() as $scene_id) {
			$copy = bkery_scene_copy()[$scene_id];
			self::assertSame($copy['label'], $data['scenes'][$scene_id]['section']);
			self::assertSame($copy['title'], $data['scenes'][$scene_id]['title']);
			self::assertSame($copy['text'], $data['scenes'][$scene_id]['text']);
			self::assertSame(array(), $data['scenes'][$scene_id]['image']);
		}
	}
}
