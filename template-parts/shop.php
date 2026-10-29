<?php
/**
 * お店について。イラスト地図の SVG はモックの図のまま残す。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title      = (string) bkery_get_theme_mod( 'bkery_shop_title' );
$bkery_open       = (string) bkery_get_theme_mod( 'bkery_shop_open' );
$bkery_close      = (string) bkery_get_theme_mod( 'bkery_shop_close' );
$bkery_cafe_open  = (string) bkery_get_theme_mod( 'bkery_shop_cafe_open' );
$bkery_cafe_close = (string) bkery_get_theme_mod( 'bkery_shop_cafe_close' );
$bkery_status     = bkery_template_hours_status_label( $bkery_open, $bkery_close );
$bkery_big        = bkery_template_clocks_are_open_range( $bkery_open, $bkery_close )
	? bkery_template_hours_range( $bkery_open, $bkery_close )
	: bkery_template_translate( '営業時間を確認してください' );
$bkery_cafe       = '';
if ( bkery_template_clock_is_valid( $bkery_cafe_open ) && bkery_template_clock_is_valid( $bkery_cafe_close ) ) {
	$bkery_cafe = bkery_template_cafe_hours_sentence( $bkery_cafe_open, $bkery_cafe_close );
}
$bkery_seats    = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_shop_seats' ) );
$bkery_parking  = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_shop_parking' ) );
$bkery_address  = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_shop_address' ) );
$bkery_access   = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_shop_access' ) );
$bkery_caption  = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_shop_map_caption' ) );
$bkery_map      = bkery_template_href_attr( (string) bkery_get_theme_mod( 'bkery_shop_map_url' ) );
$bkery_map_name = (string) get_bloginfo( 'name' );
$bkery_map_desc = $bkery_access;
if ( '' === $bkery_map_desc ) {
	$bkery_map_desc = $bkery_caption;
}
if ( '' === $bkery_map_desc ) {
	$bkery_map_desc = bkery_template_translate( '道順のイメージ図' );
}
?>
<section class="section shop" id="shop" data-section="shop" aria-labelledby="shop-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="shop-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'shop' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
	</div>

	<div class="shop__now" data-shop-now aria-live="polite">
		<span class="status-dot" data-status-dot></span>
		<div>
			<p class="shop__now-label" data-status-label><?php echo esc_html( $bkery_status ); ?></p>
			<p class="shop__now-sub" data-status-sub></p>
		</div>
	</div>

	<dl class="info">
		<div class="info__row">
			<dt><?php esc_html_e( 'ご利用時間', 'bakery-s' ); ?></dt>
			<dd><span class="info__big"><?php echo esc_html( $bkery_big ); ?></span><?php if ( '' !== $bkery_cafe ) : ?><?php echo esc_html( $bkery_cafe ); ?><?php endif; ?></dd>
		</div>
		<?php if ( '' !== $bkery_seats ) : ?>
			<div class="info__row">
				<dt><?php esc_html_e( '座席数', 'bakery-s' ); ?></dt>
				<dd><?php echo esc_html( $bkery_seats ); ?></dd>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $bkery_parking ) : ?>
			<div class="info__row">
				<dt><?php esc_html_e( '駐車場', 'bakery-s' ); ?></dt>
				<dd><?php echo esc_html( $bkery_parking ); ?></dd>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $bkery_address ) : ?>
			<div class="info__row">
				<dt><?php esc_html_e( '住所', 'bakery-s' ); ?></dt>
				<dd><span data-address><?php echo bkery_template_nl2br( $bkery_address ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></dd>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $bkery_access ) : ?>
			<div class="info__row">
				<dt><?php esc_html_e( 'アクセス', 'bakery-s' ); ?></dt>
				<dd><?php echo bkery_template_nl2br( $bkery_access ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
			</div>
		<?php endif; ?>
	</dl>

	<figure class="map" data-reveal>
		<svg class="map__svg" viewBox="0 0 360 220" role="img" aria-labelledby="map-desc">
			<title id="map-desc"><?php echo esc_html( $bkery_map_desc ); ?></title>
			<rect width="360" height="220" fill="#E7F1F8"/>
			<g fill="#D3E3F2">
				<rect x="14" y="14" width="96" height="62" rx="6"/><rect x="132" y="14" width="88" height="62" rx="6"/><rect x="242" y="14" width="104" height="62" rx="6"/>
				<rect x="14" y="104" width="96" height="102" rx="6"/><rect x="242" y="104" width="104" height="44" rx="6"/><rect x="242" y="168" width="104" height="38" rx="6"/>
			</g>
			<rect x="132" y="104" width="88" height="102" rx="6" fill="#D5E8EA"/>
			<g stroke="#F3F7FB" stroke-width="14" stroke-linecap="round">
				<path d="M0 90H360"/><path d="M121 0V220"/><path d="M231 90V220"/>
			</g>
			<path d="M48 112 V90 H231 V158 H262" fill="none" stroke="#18658F" stroke-width="3.5" stroke-dasharray="2 7" stroke-linecap="round" class="map__route"/>
			<g font-family="inherit" font-size="10.5" fill="#4E6174">
				<text x="140" y="84">△△通り</text>
				<text x="246" y="124">カフェ□□</text>
			</g>
			<g transform="translate(26 120)"><rect width="46" height="22" rx="5" fill="#152433"/><text x="23" y="15" font-size="10.5" fill="#F3F7FB" text-anchor="middle">○○駅</text></g>
			<g transform="translate(276 158)" class="map__pin">
				<circle r="15" fill="#18658F"/><circle r="5" fill="#F3F7FB"/>
			</g>
			<text x="276" y="196" font-size="11" fill="#152433" text-anchor="middle" font-weight="700"><?php echo esc_html( $bkery_map_name ); ?></text>
		</svg>
		<?php if ( '' !== $bkery_caption ) : ?>
			<figcaption><?php echo esc_html( $bkery_caption ); ?></figcaption>
		<?php endif; ?>
	</figure>

	<div class="btn-row">
		<?php if ( '' !== $bkery_map ) : ?>
			<a class="btn btn--solid" href="<?php echo $bkery_map; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-link="map"<?php echo bkery_template_is_external_href( $bkery_map ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo bkery_template_icon( 'i-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Googleマップ', 'bakery-s' ); ?><?php echo bkery_template_icon( 'i-out', 'icon icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<?php endif; ?>
		<button class="btn btn--line" type="button" data-copy-address><?php echo bkery_template_icon( 'i-copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( '住所をコピー', 'bakery-s' ); ?></button>
	</div>
</section>
