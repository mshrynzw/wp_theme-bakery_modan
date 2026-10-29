<?php
/**
 * Tests for the customizer registry.
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ThemeModRegistryTest extends TestCase {
	public function test_scene_ids_match_fallback_sources_and_copy(): void {
		$ids = bkery_scene_ids();
		self::assertSame($ids, array_keys(bkery_scene_copy()));
		self::assertSame($ids, array_keys(bkery_scene_fallback_sources()));
		self::assertCount(9, array_unique($ids));
	}

	public function test_every_setting_points_at_a_known_section_and_type(): void {
		$sections = array_keys(bkery_customizer_sections());
		$types    = array('color', 'text', 'textarea', 'html', 'url', 'clock', 'weekdays', 'image', 'video', 'checkbox', 'range');
		$registry = bkery_theme_mod_registry();

		self::assertNotEmpty($registry);
		foreach ($registry as $key => $def) {
			self::assertStringStartsWith('bkery_', $key);
			self::assertContains($def['section'], $sections);
			self::assertContains($def['type'], $types);
			self::assertNotSame('', $def['label']);
		}
	}

	public function test_color_and_scene_keys_exist(): void {
		$registry = bkery_theme_mod_registry();
		foreach (bkery_color_roles() as $role => $meta) {
			self::assertArrayHasKey('bkery_color_' . $role, $registry);
			self::assertSame('color', $registry['bkery_color_' . $role]['type']);
			self::assertStringStartsWith('--', $meta['var']);
		}

		$image_count = bkery_slot_counts()['scene_images'];
		foreach (bkery_scene_ids() as $scene_id) {
			self::assertArrayHasKey('bkery_scene_' . $scene_id . '_title', $registry);
			for ($index = 1; $index <= $image_count; $index++) {
				$key = 'bkery_scene_' . $scene_id . '_image_' . $index;
				self::assertSame(0, $registry[$key]['default']);
			}
		}
	}

	public function test_shop_clocks_match_the_mock(): void {
		$registry = bkery_theme_mod_registry();
		self::assertSame('10:00', $registry['bkery_shop_open']['default']);
		self::assertSame('18:00', $registry['bkery_shop_close']['default']);
		self::assertSame('17:00', $registry['bkery_shop_cafe_close']['default']);
		self::assertSame('', $registry['bkery_shop_closed_weekdays']['default']);
		self::assertSame(3, bkery_slot_counts()['hero']);
		self::assertSame(6, bkery_slot_counts()['gallery']);
	}

	public function test_steam_controls_default_to_the_current_look(): void {
		$registry = bkery_theme_mod_registry();
		self::assertSame('checkbox', $registry['bkery_steam_enabled']['type']);
		self::assertSame(1, $registry['bkery_steam_enabled']['default']);
		self::assertSame('bkery_steam', $registry['bkery_steam_enabled']['section']);
		self::assertSame(50, $registry['bkery_steam_density']['default']);
		self::assertSame(50, $registry['bkery_steam_speed']['default']);
		self::assertSame(100, $registry['bkery_steam_column']['default']);
		self::assertSame(100, $registry['bkery_steam_background']['default']);
		foreach (array('bkery_steam_density', 'bkery_steam_speed', 'bkery_steam_column', 'bkery_steam_background') as $key) {
			self::assertSame('range', $registry[$key]['type']);
		}
	}

	public function test_steam_values_stay_on_or_within_zero_to_one_hundred(): void {
		self::assertSame(1, bkery_steam_flag(true));
		self::assertSame(1, bkery_steam_flag('1'));
		self::assertSame(1, bkery_steam_flag('on'));
		self::assertSame(0, bkery_steam_flag(false));
		self::assertSame(0, bkery_steam_flag('0'));
		self::assertSame(0, bkery_steam_flag(''));
		self::assertSame(0, bkery_steam_percent(-4, 100));
		self::assertSame(100, bkery_steam_percent(140, 50));
		self::assertSame(50, bkery_steam_percent('nope', 50));
		self::assertSame(80, bkery_steam_percent('79.6', 0));
		self::assertSame(
			array(
				'enabled'    => 1,
				'density'    => 50,
				'speed'      => 50,
				'column'     => 100,
				'background' => 100,
			),
			bkery_steam_frontend()
		);
	}
}
