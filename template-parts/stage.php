<?php
/**
 * PC・タブレット用の背景ステージ。画像はフロントが幅 768px 以上のときだけ読む。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_open  = (string) bkery_get_theme_mod( 'bkery_shop_open' );
$bkery_close = (string) bkery_get_theme_mod( 'bkery_shop_close' );
$bkery_label = bkery_template_hours_status_label( $bkery_open, $bkery_close );
?>
<div class="stage" data-stage aria-hidden="true">
	<div class="stage__media">
		<div class="stage__layer"><img alt="" decoding="async"></div>
		<div class="stage__layer"><img alt="" decoding="async"></div>
	</div>
	<div class="stage__shade"></div>
	<div class="stage__caption">
		<p class="stage__section" data-stage-section></p>
		<p class="stage__title" data-stage-title></p>
		<p class="stage__text" data-stage-text></p>
	</div>
	<div class="stage__ticker">
		<span class="status-dot" data-status-dot></span>
		<span data-status-label><?php echo esc_html( $bkery_label ); ?></span>
		<span class="stage__ticker-sep"></span>
		<span data-next-bake-short></span>
	</div>
</div>
