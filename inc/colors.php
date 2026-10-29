<?php
/**
 * カスタマイザーの色をモックと同じ CSS 変数にする。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * レジストリに書いてある 11 色。標準プリセットと初期化の戻り先。
 *
 * @return array<string, string>
 */
function bkery_default_color_palette(): array {
	$registry = bkery_theme_mod_registry();
	$palette  = array();
	foreach ( array_keys( bkery_color_roles() ) as $role ) {
		$key              = 'bkery_color_' . $role;
		$palette[ $role ] = isset( $registry[ $key ]['default'] ) ? (string) $registry[ $key ]['default'] : '';
	}

	return $palette;
}

/**
 * テーマ同梱の配色。各配色は bkery_color_roles() の全ロールを持つ。
 *
 * @return array<string, array{label:string, colors:array<string, string>}>
 */
function bkery_color_presets(): array {
	return array(
		'standard' => array(
			'label'  => '標準',
			'colors' => bkery_default_color_palette(),
		),
		'morning'  => array(
			'label'  => '朝の光',
			'colors' => array(
				'flour'      => '#FFF8F1',
				'paper'      => '#FFFFFF',
				'kraft'      => '#F3E6CF',
				'kraft_deep' => '#E7D3B2',
				'crust'      => '#C4845A',
				'crust_soft' => '#E0B08A',
				'sage'       => '#7A947C',
				'ink'        => '#43362C',
				'rye'        => '#8D7868',
				'glow'       => '#F6D3B4',
				'espresso'   => '#5C4A3E',
			),
		),
		'rye'      => array(
			'label'  => 'ライ麦',
			'colors' => array(
				'flour'      => '#F3EBDD',
				'paper'      => '#F8F3EA',
				'kraft'      => '#CDB58A',
				'kraft_deep' => '#B79A68',
				'crust'      => '#6E3B22',
				'crust_soft' => '#9A5A38',
				'sage'       => '#5A6848',
				'ink'        => '#241C16',
				'rye'        => '#4E3C30',
				'glow'       => '#E2B48C',
				'espresso'   => '#16110E',
			),
		),
		'matcha'   => array(
			'label'  => '抹茶',
			'colors' => array(
				'flour'      => '#F4F5EF',
				'paper'      => '#FBFCF8',
				'kraft'      => '#E3DCC8',
				'kraft_deep' => '#CFC6AE',
				'crust'      => '#8A5A3A',
				'crust_soft' => '#C49274',
				'sage'       => '#2F6A45',
				'ink'        => '#242820',
				'rye'        => '#5E6558',
				'glow'       => '#E4D0B0',
				'espresso'   => '#1A2420',
			),
		),
	);
}

/**
 * カスタマイザーの配色コントロールへ渡すデータ。
 *
 * @return array{roles:array<string, array{setting:string, default:string}>, presets:array<string, array{label:string, colors:array<string, string>}>, i18n:array{choose:string, confirmReset:string}}
 */
function bkery_color_customize_payload(): array {
	$defaults = bkery_default_color_palette();
	$roles    = array();
	foreach ( array_keys( bkery_color_roles() ) as $role ) {
		$roles[ $role ] = array(
			'setting' => 'bkery_color_' . $role,
			'default' => $defaults[ $role ] ?? '',
		);
	}

	return array(
		'roles'   => $roles,
		'presets' => bkery_color_presets(),
		'i18n'    => array(
			'choose'       => '配色を選んでください。',
			'confirmReset' => '色をテーマの標準配色に戻しますか？',
		),
	);
}

/**
 * ロール名と色から :root の宣言を作る。6桁の16進だけを採用する。
 *
 * @param array<string, string> $colors ロール => #RRGGBB。
 */
function bkery_color_declaration_block( array $colors ): string {
	$rules = array();
	foreach ( bkery_color_roles() as $role => $meta ) {
		if ( ! isset( $colors[ $role ] ) || ! is_string( $colors[ $role ] ) ) {
			continue;
		}
		if ( ! preg_match( '/^#[0-9A-Fa-f]{6}$/', $colors[ $role ] ) ) {
			continue;
		}
		$rules[] = $meta['var'] . ':' . $colors[ $role ];
	}
	if ( array() === $rules ) {
		return '';
	}

	return ':root{' . implode( ';', $rules ) . ';}';
}

/**
 * 現在のテーマ設定から色の上書き CSS を返す。
 */
function bkery_get_color_palette_css(): string {
	$colors = array();
	foreach ( array_keys( bkery_color_roles() ) as $role ) {
		$value           = bkery_get_theme_mod( 'bkery_color_' . $role );
		$colors[ $role ] = is_string( $value ) ? $value : '';
	}

	return bkery_color_declaration_block( $colors );
}
