<?php
/**
 * PC 用のページ内目次。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_site_name = (string) get_bloginfo( 'name' );
$bkery_home_href = bkery_template_href_attr( bkery_template_current_anchor( 'hero' ) );
?>
<nav class="rail" aria-label="<?php esc_attr_e( 'ページ内目次', 'bakery-s' ); ?>">
	<a class="rail__logo" href="<?php echo $bkery_home_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bkery_template_href_attr() ?>"><span><?php echo esc_html( $bkery_site_name ); ?></span></a>
	<ol class="rail__list">
		<?php foreach ( bkery_template_rail_links() as $bkery_id => $bkery_label ) : ?>
			<?php $bkery_href = bkery_template_href_attr( bkery_template_current_anchor( $bkery_id ) ); ?>
			<li><a href="<?php echo $bkery_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bkery_template_href_attr() ?>" data-nav="<?php echo esc_attr( $bkery_id ); ?>"><?php echo esc_html( bkery_template_translate( $bkery_label ) ); ?></a></li>
		<?php endforeach; ?>
	</ol>
</nav>
