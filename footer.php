<?php
/**
 * フッター、固定ナビ、トップだけのダイアログ。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_site_name = (string) get_bloginfo( 'name' );
$bkery_note      = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_footer_note' ) );
?>
<footer class="footer">
	<p class="footer__logo"><?php echo esc_html( $bkery_site_name ); ?></p>
	<?php if ( '' !== $bkery_note ) : ?>
		<p class="footer__note"><?php echo bkery_template_nl2br( $bkery_note ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></p>
	<?php endif; ?>
	<p class="footer__copy"><small><?php echo esc_html( sprintf( '© %s All Rights Reserved.', $bkery_site_name ) ); ?></small></p>
</footer>
</div>
<?php get_template_part( 'template-parts/dock' ); ?>
<?php
if ( function_exists( 'is_front_page' ) && is_front_page() ) {
	get_template_part( 'template-parts/dialogs' );
}
wp_footer();
?>
</body>
</html>
