<?php
/**
 * Instagram と締めのコピー。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title  = (string) bkery_get_theme_mod( 'bkery_instagram_title' );
$bkery_lead   = (string) bkery_get_theme_mod( 'bkery_instagram_lead' );
$bkery_count  = function_exists( 'bkery_slot_counts' ) ? (int) bkery_slot_counts()['instagram'] : 6;
$bkery_url    = bkery_template_href_attr( (string) bkery_get_theme_mod( 'bkery_link_instagram' ) );
$bkery_handle = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_link_instagram_handle' ) );
$bkery_copy   = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_closing_copy' ) );
$bkery_text   = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_closing_text' ) );
$bkery_bake   = bkery_template_href_attr( bkery_template_current_anchor( 'bake' ) );
$bkery_shop   = bkery_template_href_attr( bkery_template_current_anchor( 'shop' ) );
?>
<section class="section insta" id="instagram" data-section="instagram" aria-labelledby="insta-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="insta-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'instagram' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
		<?php if ( '' !== bkery_template_visible_text( $bkery_lead ) ) : ?>
			<p class="section__lead"><?php echo bkery_template_nl2br( $bkery_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>

	<ul class="insta__grid" aria-label="<?php esc_attr_e( '投稿イメージ', 'bakery-s' ); ?>">
		<?php for ( $bkery_index = 1; $bkery_index <= $bkery_count; $bkery_index++ ) : ?>
			<?php
			$bkery_image_id = absint( bkery_get_theme_mod( 'bkery_instagram_image_' . $bkery_index ) );
			$bkery_label    = bkery_template_media_label( $bkery_image_id, 'instagram-' . $bkery_index );
			$bkery_image    = bkery_template_image_html( $bkery_image_id, 'bkery-card' );
			?>
			<li class="ph" data-label="<?php echo esc_attr( $bkery_label ); ?>"><?php echo $bkery_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
		<?php endfor; ?>
	</ul>

	<?php if ( '' !== $bkery_url ) : ?>
		<a class="btn btn--insta" href="<?php echo $bkery_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-link="instagram"<?php echo bkery_template_is_external_href( $bkery_url ) ? ' target="_blank" rel="noopener"' : ''; ?>>
			<?php echo bkery_template_icon( 'i-insta' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php if ( '' !== $bkery_handle ) : ?><span data-insta-handle><?php echo esc_html( $bkery_handle ); ?></span><?php else : ?><span data-insta-handle></span><?php endif; ?> <?php esc_html_e( 'をフォローする', 'bakery-s' ); ?></span>
			<?php echo bkery_template_icon( 'i-out', 'icon icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	<?php endif; ?>

	<div class="closing">
		<?php if ( '' !== $bkery_copy ) : ?>
			<p class="closing__copy"><?php echo bkery_template_nl2br( $bkery_copy ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
		<?php if ( '' !== $bkery_text ) : ?>
			<p class="closing__text"><?php echo bkery_template_nl2br( $bkery_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
		<div class="btn-row">
			<a class="btn btn--light" href="<?php echo $bkery_bake; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( '焼き上がり時間', 'bakery-s' ); ?></a>
			<a class="btn btn--light" href="<?php echo $bkery_shop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'アクセス', 'bakery-s' ); ?></a>
		</div>
	</div>
</section>
