<?php
/**
 * 店主・お店の想い。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title    = (string) bkery_get_theme_mod( 'bkery_owner_title' );
$bkery_heading  = (string) bkery_get_theme_mod( 'bkery_owner_heading' );
$bkery_name     = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_owner_name' ) );
$bkery_kana     = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_owner_kana' ) );
$bkery_photo    = absint( bkery_get_theme_mod( 'bkery_owner_photo' ) );
$bkery_bio      = (string) bkery_get_theme_mod( 'bkery_owner_bio' );
$bkery_message_title = (string) bkery_get_theme_mod( 'bkery_owner_message_title' );
$bkery_message  = (string) bkery_get_theme_mod( 'bkery_owner_message' );
$bkery_sign     = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_owner_sign' ) );
$bkery_label    = bkery_template_media_label( $bkery_photo, 'owner' );
$bkery_image    = bkery_template_image_html( $bkery_photo, 'bkery-owner', false, '' !== $bkery_name ? $bkery_name : null );
$bkery_has_message = '' !== bkery_template_visible_text( $bkery_message_title )
	|| '' !== bkery_template_visible_text( wp_strip_all_tags( $bkery_message ) )
	|| '' !== $bkery_sign;
?>
<section class="section section--kraft owner" id="owner" data-section="owner" aria-labelledby="owner-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="owner-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'owner' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
	</div>

	<div class="owner__profile">
		<figure class="owner__photo ph" data-label="<?php echo esc_attr( $bkery_label ); ?>" data-reveal>
			<?php echo $bkery_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</figure>
		<div class="owner__name">
			<?php if ( '' !== bkery_template_visible_text( $bkery_heading ) ) : ?>
				<h3><?php echo esc_html( $bkery_heading ); ?></h3>
			<?php endif; ?>
			<?php if ( '' !== $bkery_name || '' !== $bkery_kana ) : ?>
				<p class="owner__person"><?php echo esc_html( $bkery_name ); ?><?php if ( '' !== $bkery_kana ) : ?><span><?php echo esc_html( $bkery_kana ); ?></span><?php endif; ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( '' !== bkery_template_visible_text( $bkery_bio ) ) : ?>
		<div class="prose">
			<?php echo wp_kses_post( wpautop( esc_html( $bkery_bio ) ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $bkery_has_message ) : ?>
		<div class="owner__message">
			<?php if ( '' !== bkery_template_visible_text( $bkery_message_title ) ) : ?>
				<h3><?php echo esc_html( $bkery_message_title ); ?></h3>
			<?php endif; ?>
			<blockquote class="prose">
				<?php if ( '' !== bkery_template_visible_text( wp_strip_all_tags( $bkery_message ) ) ) : ?>
					<?php echo wp_kses_post( wpautop( $bkery_message ) ); ?>
				<?php endif; ?>
				<?php if ( '' !== $bkery_sign ) : ?>
					<p class="owner__sign"><?php echo bkery_template_nl2br( $bkery_sign ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
			</blockquote>
		</div>
	<?php endif; ?>
</section>
