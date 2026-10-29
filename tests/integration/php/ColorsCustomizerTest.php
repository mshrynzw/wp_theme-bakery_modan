<?php
/**
 * Integration tests for the color preset customizer control.
 *
 * @package bakery-s
 */

/**
 * 配色コントロールが色セクションの先頭に登録されること。
 */
class ColorsCustomizerTest extends WP_UnitTestCase {
	public function test_presets_control_is_registered_above_the_color_pickers(): void {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$wp_customize = new WP_Customize_Manager();
		$wp_customize->wp_loaded();

		$presets = $wp_customize->get_control( 'bkery_color_presets' );
		self::assertInstanceOf( Bkery_Customize_Color_Presets_Control::class, $presets );
		self::assertSame( 'bkery_color_presets', $presets->type );
		self::assertSame( 'bkery_colors', $presets->section );
		self::assertSame( array(), $presets->settings );

		$flour = $wp_customize->get_control( 'bkery_color_flour' );
		self::assertNotNull( $flour );
		self::assertLessThan( $flour->priority, $presets->priority );
		self::assertSame( '#F3F7FB', $wp_customize->get_setting( 'bkery_color_flour' )->default );

		foreach ( array_keys( bkery_color_roles() ) as $role ) {
			self::assertNotNull( $wp_customize->get_setting( 'bkery_color_' . $role ) );
		}
	}

	public function test_customize_controls_script_is_enqueued(): void {
		do_action( 'customize_controls_enqueue_scripts' );

		self::assertTrue( wp_script_is( 'bkery-customize-controls', 'enqueued' ) );
		self::assertTrue( wp_style_is( 'bkery-customize-controls', 'enqueued' ) );
		$localized = wp_scripts()->get_data( 'bkery-customize-controls', 'data' );
		self::assertIsString( $localized );
		self::assertStringContainsString( 'bkeryColorCustomize', $localized );
		self::assertStringContainsString( 'matcha', $localized );
	}
}
