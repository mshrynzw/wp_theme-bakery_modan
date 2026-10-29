<?php
/**
 * Tests for builtin color presets.
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ColorPaletteTest extends TestCase {
	public function test_standard_preset_matches_registry_defaults(): void {
		$registry = bkery_theme_mod_registry();
		$standard = bkery_color_presets()['standard']['colors'];

		foreach (bkery_color_roles() as $role => $meta) {
			self::assertStringStartsWith('--', $meta['var']);
			self::assertSame($registry['bkery_color_' . $role]['default'], $standard[$role]);
			self::assertSame($standard[$role], bkery_default_color_palette()[$role]);
		}
	}

	public function test_every_preset_covers_every_role_with_hex(): void {
		$roles = array_keys(bkery_color_roles());
		self::assertGreaterThanOrEqual(2, count(bkery_color_presets()));

		foreach (bkery_color_presets() as $preset_id => $preset) {
			self::assertNotSame('', $preset['label'], $preset_id);
			self::assertSame($roles, array_keys($preset['colors']), $preset_id);
			foreach ($preset['colors'] as $role => $color) {
				self::assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $color, $preset_id . ':' . $role);
			}
		}
	}

	public function test_alternate_presets_differ_from_standard(): void {
		$standard = bkery_color_presets()['standard']['colors'];
		foreach (array('morning', 'rye', 'matcha') as $preset_id) {
			self::assertNotSame($standard, bkery_color_presets()[$preset_id]['colors']);
		}
	}

	public function test_customize_payload_points_at_color_settings(): void {
		$payload = bkery_color_customize_payload();

		foreach (array_keys(bkery_color_roles()) as $role) {
			self::assertSame('bkery_color_' . $role, $payload['roles'][$role]['setting']);
			self::assertSame(bkery_default_color_palette()[$role], $payload['roles'][$role]['default']);
		}

		self::assertSame(array_keys(bkery_color_presets()), array_keys($payload['presets']));
		self::assertNotSame('', $payload['i18n']['choose']);
		self::assertNotSame('', $payload['i18n']['confirmReset']);
	}
}
