<?php
/**
 * スマホ用の固定ナビゲーション。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;
?>
<nav class="dock" data-dock aria-label="<?php esc_attr_e( '主要メニュー', 'bakery-s' ); ?>">
	<?php foreach ( bkery_template_dock_links() as $bkery_item ) : ?>
		<?php $bkery_href = bkery_template_href_attr( bkery_template_current_anchor( $bkery_item['id'] ) ); ?>
		<a href="<?php echo $bkery_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bkery_template_href_attr() ?>" data-nav="<?php echo esc_attr( $bkery_item['id'] ); ?>"><?php echo bkery_template_icon( $bkery_item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( bkery_template_translate( $bkery_item['label'] ) ); ?></a>
	<?php endforeach; ?>
</nav>
