<?php
/**
 * Tests for color CSS declarations.
 *
 * @package bakery-s
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ColorCssTest extends TestCase {
	public function test_declaration_uses_mock_variable_names_in_role_order(): void {
		$css = bkery_color_declaration_block(
			array(
				'flour' => '#FBF7F0',
				'ink'   => '#33281F',
				'crust' => 'red',
			)
		);

		self::assertSame(':root{--flour:#FBF7F0;--ink:#33281F;}', $css);
	}

	public function test_empty_when_nothing_is_valid(): void {
		self::assertSame('', bkery_color_declaration_block(array('flour' => '')));
	}
}
