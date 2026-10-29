<?php
/**
 * テンプレート表示分岐の単体テスト。
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/template-parts/markup.php';

final class TemplateMarkupTest extends TestCase {
	public function test_price_is_hidden_when_zero_or_less(): void {
		self::assertSame('', bkery_template_price_html(0));
		self::assertSame('', bkery_template_price_html(-1));
	}

	public function test_price_includes_grouped_number_and_yen(): void {
		self::assertSame('1,200<small>円</small>', bkery_template_price_html(1200));
	}

	public function test_href_keeps_hash_and_http_and_drops_scripts(): void {
		self::assertSame('#bake', bkery_template_href(' #bake '));
		self::assertSame('https://example.com/path', bkery_template_href('https://example.com/path'));
		self::assertSame('', bkery_template_href('javascript:alert(1)'));
		self::assertSame('', bkery_template_href('https://example.com/a" onclick="1"'));
		self::assertSame('', bkery_template_href(''));
	}

	public function test_point_link_requires_label_and_safe_url(): void {
		self::assertSame(
			array(
				'label'    => '今日の焼き上がり時間を見る',
				'href'     => '#bake',
				'external' => false,
			),
			bkery_template_point_link(' 今日の焼き上がり時間を見る ', '#bake')
		);
		self::assertTrue(bkery_template_point_link('地図', 'https://maps.example/')['external']);
		self::assertSame(array(), bkery_template_point_link('', '#bake'));
		self::assertSame(array(), bkery_template_point_link('地図', 'javascript:alert(1)'));
	}

	public function test_hours_label_uses_clocks_and_rejects_invalid_range(): void {
		self::assertSame('営業時間 10:00〜18:00', bkery_template_hours_status_label('10:00', '18:00'));
		self::assertSame('営業時間を確認してください', bkery_template_hours_status_label('18:00', '10:00'));
		self::assertSame('営業時間を確認してください', bkery_template_hours_status_label('9:00', '18:00'));
		self::assertSame('10:00〜18:00', bkery_template_hours_range('10:00', '18:00'));
		self::assertSame(
			'カフェのご利用は10:00〜17:00までとなります。',
			bkery_template_cafe_hours_sentence('10:00', '17:00')
		);
	}

	public function test_visible_text_and_line_breaks_are_escaped(): void {
		self::assertSame('', bkery_template_visible_text("  \n"));
		self::assertSame('一行', bkery_template_visible_text(' 一行 '));
		self::assertSame("A&amp;B<br>C", bkery_template_nl2br("A&B\nC"));
	}

	public function test_anchor_stays_on_page_or_prefixes_front_url(): void {
		self::assertSame('#shop', bkery_template_anchor('shop', true, 'https://example.com/home/'));
		self::assertSame('https://example.com/home/#bake', bkery_template_anchor('#bake', false, 'https://example.com/home/'));
		self::assertSame('', bkery_template_anchor('bad fragment', false, 'https://example.com/'));
	}

	public function test_gallery_slots_skip_empty_frames(): void {
		$visible = bkery_template_visible_gallery_slots(
			array(
				array(
					'image_id' => 0,
					'caption'  => '  ',
					'label'    => 'empty',
				),
				array(
					'image_id' => 4,
					'caption'  => '棚',
					'label'    => 'gallery-1',
				),
				array(
					'image_id' => 0,
					'caption'  => '窓ぎわ',
					'label'    => 'gallery-2',
				),
			)
		);

		self::assertSame(
			array(
				array(
					'image_id' => 4,
					'caption'  => '棚',
					'label'    => 'gallery-1',
				),
				array(
					'image_id' => 0,
					'caption'  => '窓ぎわ',
					'label'    => 'gallery-2',
				),
			),
			$visible
		);
	}

	public function test_bake_schedule_keeps_order_and_drops_blank_times(): void {
		$schedule = bkery_template_bake_schedule(
			array(
				'bakeSchedule' => array(
					array(
						'time'  => '11:00',
						'items' => array(' focaccia ', ''),
					),
					array(
						'time'  => '',
						'items' => array('skip'),
					),
					array(
						'time'  => '10:00',
						'items' => array('croissant'),
					),
				),
			)
		);

		self::assertSame(
			array(
				array(
					'time'  => '11:00',
					'items' => array('focaccia'),
				),
				array(
					'time'  => '10:00',
					'items' => array('croissant'),
				),
			),
			$schedule
		);
	}

	public function test_bake_chips_skip_unknown_products_and_mark_seasonal(): void {
		$chips = bkery_template_bake_chips(
			array('missing', 'croissant', 'season-croque'),
			array(
				'croissant'     => array(
					'name'     => 'クロワッサン',
					'seasonal' => false,
				),
				'season-croque' => array(
					'name'     => 'クロックムッシュ',
					'seasonal' => true,
				),
			)
		);

		self::assertSame('croissant', $chips[0]['id']);
		self::assertFalse($chips[0]['seasonal']);
		self::assertTrue($chips[1]['seasonal']);

		$regular = bkery_template_chip_html($chips[0]);
		$season  = bkery_template_chip_html(
			array(
				'id'       => 'season-croque',
				'name'     => '<期間>',
				'seasonal' => true,
			)
		);

		self::assertStringNotContainsString('dot-season', $regular);
		self::assertStringNotContainsString('aria-label', $regular);
		self::assertStringContainsString('class="dot-season"', $season);
		self::assertStringContainsString('aria-label="期間限定 &lt;期間&gt;"', $season);
		self::assertStringContainsString('&lt;期間&gt;', $season);
		self::assertStringNotContainsString('<期間>', $season);
	}

	public function test_product_bake_times_prefer_schedule_order(): void {
		$schedule = array(
			array(
				'time'  => '10:00',
				'items' => array('croissant'),
			),
			array(
				'time'  => '15:00',
				'items' => array('croissant', 'muffin'),
			),
		);

		self::assertSame(
			array('10:00', '15:00'),
			bkery_template_product_bake_times($schedule, 'croissant', '15:00,10:00')
		);
		self::assertSame(
			array('15:00', '10:00'),
			bkery_template_product_bake_times($schedule, 'shokupan', '15:00,10:00')
		);
	}

	public function test_menu_tabs_count_all_and_escape_labels(): void {
		$counts = bkery_template_menu_counts(
			3,
			bkery_template_count_by_slug(array('meal', '', 'sweet', 'meal'))
		);

		self::assertSame(
			array(
				'all'   => 3,
				'meal'  => 2,
				'sweet' => 1,
			),
			$counts
		);

		$html = bkery_template_menu_tab_html('meal', '<食事>', 2, false);
		self::assertStringContainsString('data-category="meal"', $html);
		self::assertStringContainsString('data-cat="meal"', $html);
		self::assertStringContainsString('aria-pressed="false"', $html);
		self::assertStringContainsString('&lt;食事&gt;', $html);
		self::assertStringContainsString('<span>2</span>', $html);
	}

	public function test_season_card_matches_mock_classes_and_hides_zero_price(): void {
		$html = bkery_template_season_card_html(
			array(
				'slug'       => 'season-croque',
				'name'       => 'クロック',
				'excerpt'    => '一行',
				'price'      => 0,
				'image_html' => '',
				'label'      => 'season-1',
				'index'      => 1,
				'total'      => 2,
			)
		);

		self::assertStringContainsString('class="carousel__slide"', $html);
		self::assertStringContainsString('class="season-card"', $html);
		self::assertStringContainsString('data-product="season-croque"', $html);
		self::assertStringContainsString('class="tag tag--season"', $html);
		self::assertStringContainsString('class="season-card__more"', $html);
		self::assertStringContainsString('aria-label="1 / 2"', $html);
		self::assertStringNotContainsString('class="price"', $html);
		self::assertStringNotContainsString('<img', $html);
	}

	public function test_menu_card_matches_mock_classes(): void {
		$html = bkery_template_menu_card_html(
			array(
				'slug'       => 'focaccia',
				'name'       => 'フォカッチャ',
				'category'   => 'meal',
				'price'      => 999,
				'image_html' => '<img src="https://example.com/a.webp" alt="">',
				'label'      => 'focaccia',
			)
		);

		self::assertStringContainsString('data-category="meal"', $html);
		self::assertStringContainsString('class="menu-card"', $html);
		self::assertStringContainsString('class="tag tag--regular"', $html);
		self::assertStringContainsString('定番', $html);
		self::assertStringContainsString('<span class="price">999<small>円</small></span>', $html);
		self::assertStringContainsString('<img src="https://example.com/a.webp" alt="">', $html);
	}

	public function test_nav_labels_follow_the_design_table(): void {
		$rail = bkery_template_rail_links();
		self::assertSame(
			array('hero', 'concept', 'gallery', 'season', 'menu', 'bake', 'owner', 'shop', 'instagram'),
			array_keys($rail)
		);
		self::assertSame('店内のようす', $rail['gallery']);

		$dock = bkery_template_dock_links();
		self::assertCount(5, $dock);
		self::assertSame('season', $dock[0]['id']);
		self::assertSame('季節限定', $dock[0]['label']);
		self::assertSame('instagram', $dock[4]['id']);
		self::assertSame('i-cup', bkery_template_section_icon('gallery'));
		self::assertSame('i-clock', bkery_template_point_icon(2));
	}

	public function test_video_placeholder_when_url_is_empty(): void {
		self::assertTrue(bkery_template_should_show_video_placeholder(''));
		self::assertTrue(bkery_template_should_show_video_placeholder('images/local.mp4'));
		self::assertFalse(bkery_template_should_show_video_placeholder('https://example.com/bake.mp4'));
	}

	public function test_media_label_falls_back_without_a_file(): void {
		self::assertSame('hero-1', bkery_template_media_label(0, 'hero-1'));
		self::assertSame('hero-2', bkery_template_media_label(12, 'hero-2'));
	}

	public function test_category_slug_reads_term_or_array(): void {
		$term       = new stdClass();
		$term->slug = 'sweet';
		self::assertSame('sweet', bkery_template_primary_category_slug(array($term)));
		self::assertSame('drink', bkery_template_primary_category_slug(array(array('slug' => 'drink'))));
		self::assertSame('', bkery_template_primary_category_slug(array()));
		self::assertFalse(bkery_template_is_seasonal(''));
		self::assertFalse(bkery_template_is_seasonal('regular'));
		self::assertTrue(bkery_template_is_seasonal('seasonal'));
	}
}
