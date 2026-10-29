<?php
/**
 * 外観 > カスタマイズ。設定の中身は inc/theme-mods.php が持つ。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * レジストリの型に合わせて保存値を整える。
 *
 * @param mixed               $value   入力値。
 * @param WP_Customize_Setting $setting 設定オブジェクト。
 * @return mixed
 */
function bkery_sanitize_theme_mod( $value, $setting ) {
	$key      = is_object( $setting ) && isset( $setting->id ) ? (string) $setting->id : '';
	$registry = bkery_theme_mod_registry();
	$def      = $registry[ $key ] ?? null;
	$type     = is_array( $def ) ? (string) $def['type'] : 'text';
	$default  = is_array( $def ) ? $def['default'] : '';

	switch ( $type ) {
		case 'color':
			$color = sanitize_hex_color( is_string( $value ) ? $value : '' );
			return $color ? $color : $default;
		case 'textarea':
			return sanitize_textarea_field( is_string( $value ) ? $value : '' );
		case 'html':
			return wp_kses_post( is_string( $value ) ? $value : '' );
		case 'url':
			return esc_url_raw( is_string( $value ) ? $value : '' );
		case 'clock':
			$clock = bkery_sanitize_clock( is_string( $value ) ? $value : '' );
			return '' === $clock ? (string) $default : $clock;
		case 'weekdays':
			return bkery_format_closed_weekdays( bkery_parse_closed_weekdays( is_string( $value ) ? $value : '' ) );
		case 'image':
		case 'video':
			return absint( $value );
		case 'checkbox':
			return bkery_steam_flag( $value );
		case 'range':
			$fallback = is_numeric( $default ) ? (int) $default : 0;
			return bkery_steam_percent( $value, $fallback );
		default:
			return sanitize_text_field( is_string( $value ) ? $value : '' );
	}
}

/**
 * セクションと設定を一括登録する。
 *
 * @param WP_Customize_Manager $wp_customize カスタマイザー。
 */
function bkery_customize_register( $wp_customize ) {
	require_once get_theme_file_path( '/inc/customize/class-bkery-customize-color-presets-control.php' );

	foreach ( bkery_customizer_sections() as $id => $section ) {
		$wp_customize->add_section(
			$id,
			array(
				'title'       => $section['title'],
				'description' => $section['description'],
				'priority'    => $section['priority'],
			)
		);
	}

	$wp_customize->add_control(
		new Bkery_Customize_Color_Presets_Control(
			$wp_customize,
			'bkery_color_presets',
			array(
				'label'       => '配色',
				'description' => '選んだ配色を11色へまとめて入れます。公開するまで保存されません。',
				'section'     => 'bkery_colors',
				'priority'    => 1,
				'settings'    => array(),
			)
		)
	);

	foreach ( bkery_theme_mod_registry() as $key => $def ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $def['default'],
				'sanitize_callback' => 'bkery_sanitize_theme_mod',
				'type'              => 'theme_mod',
			)
		);

		$control = array(
			'label'    => $def['label'],
			'section'  => $def['section'],
			'settings' => $key,
		);
		if ( '' !== $def['description'] ) {
			$control['description'] = $def['description'];
		}

		switch ( $def['type'] ) {
			case 'color':
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, $control ) );
				break;
			case 'image':
				$wp_customize->add_control(
					new WP_Customize_Media_Control(
						$wp_customize,
						$key,
						array_merge( $control, array( 'mime_type' => 'image' ) )
					)
				);
				break;
			case 'video':
				$wp_customize->add_control(
					new WP_Customize_Media_Control(
						$wp_customize,
						$key,
						array_merge( $control, array( 'mime_type' => 'video' ) )
					)
				);
				break;
			case 'textarea':
			case 'html':
				$wp_customize->add_control( $key, array_merge( $control, array( 'type' => 'textarea' ) ) );
				break;
			case 'url':
				$wp_customize->add_control( $key, array_merge( $control, array( 'type' => 'url' ) ) );
				break;
			case 'checkbox':
				$wp_customize->add_control( $key, array_merge( $control, array( 'type' => 'checkbox' ) ) );
				break;
			case 'range':
				$wp_customize->add_control(
					$key,
					array_merge(
						$control,
						array(
							'type'        => 'range',
							'input_attrs' => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 1,
							),
						)
					)
				);
				break;
			default:
				$wp_customize->add_control( $key, $control );
				break;
		}
	}
}
add_action( 'customize_register', 'bkery_customize_register' );

/**
 * 配色の適用と初期化に使うスクリプトを、カスタマイザー画面だけへ読み込む。
 */
function bkery_customize_controls_enqueue(): void {
	$js_path = get_theme_file_path( '/assets/js/customize-controls.js' );
	if ( ! file_exists( $js_path ) ) {
		return;
	}

	wp_enqueue_script(
		'bkery-customize-controls',
		get_theme_file_uri( '/assets/js/customize-controls.js' ),
		array( 'customize-controls' ),
		(string) filemtime( $js_path ),
		true
	);
	wp_localize_script( 'bkery-customize-controls', 'bkeryColorCustomize', bkery_color_customize_payload() );

	$css_path = get_theme_file_path( '/assets/css/customize-controls.css' );
	if ( ! file_exists( $css_path ) ) {
		return;
	}

	wp_enqueue_style(
		'bkery-customize-controls',
		get_theme_file_uri( '/assets/css/customize-controls.css' ),
		array(),
		(string) filemtime( $css_path )
	);
}
add_action( 'customize_controls_enqueue_scripts', 'bkery_customize_controls_enqueue' );
