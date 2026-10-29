<?php
/**
 * お店のコンセプト。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title = (string) bkery_get_theme_mod( 'bkery_concept_title' );
$bkery_copy  = (string) bkery_get_theme_mod( 'bkery_concept_copy' );
$bkery_body  = (string) bkery_get_theme_mod( 'bkery_concept_body' );
$bkery_photo = absint( bkery_get_theme_mod( 'bkery_concept_photo' ) );
$bkery_points_title = (string) bkery_get_theme_mod( 'bkery_points_title' );
$bkery_label = bkery_template_media_label( $bkery_photo, 'concept' );
$bkery_image = bkery_template_image_html( $bkery_photo, 'bkery-gallery' );
?>
<section class="section concept" id="concept" data-section="concept" aria-labelledby="concept-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="concept-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'concept' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
	</div>

	<?php if ( '' !== bkery_template_visible_text( $bkery_copy ) ) : ?>
		<p class="concept__copy" data-reveal><?php echo bkery_template_nl2br( $bkery_copy ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>

	<figure class="concept__photo ph drift" data-label="<?php echo esc_attr( $bkery_label ); ?>" data-reveal>
		<?php echo $bkery_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</figure>

	<?php if ( '' !== bkery_template_visible_text( wp_strip_all_tags( $bkery_body ) ) ) : ?>
		<div class="concept__body prose">
			<?php echo wp_kses_post( wpautop( $bkery_body ) ); ?>
		</div>
	<?php endif; ?>

	<div class="points" aria-labelledby="points-title">
		<h3 class="points__title" id="points-title"><?php echo esc_html( $bkery_points_title ); ?></h3>
		<ul class="points__list">
			<?php for ( $bkery_index = 1; $bkery_index <= 3; $bkery_index++ ) : ?>
				<?php
				$bkery_point_title = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_point_' . $bkery_index . '_title' ) );
				$bkery_point_body  = (string) bkery_get_theme_mod( 'bkery_point_' . $bkery_index . '_body' );
				$bkery_point_link  = bkery_template_point_link(
					(string) bkery_get_theme_mod( 'bkery_point_' . $bkery_index . '_link_label' ),
					(string) bkery_get_theme_mod( 'bkery_point_' . $bkery_index . '_link_url' )
				);
				if ( '' === $bkery_point_title && '' === bkery_template_visible_text( wp_strip_all_tags( $bkery_point_body ) ) && array() === $bkery_point_link ) {
					continue;
				}
				$bkery_icon = bkery_template_point_icon( $bkery_index );
				?>
				<li class="point">
					<?php echo bkery_template_icon( $bkery_icon, 'icon point__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div>
						<?php if ( '' !== $bkery_point_title ) : ?>
							<h4><?php echo esc_html( $bkery_point_title ); ?></h4>
						<?php endif; ?>
						<?php if ( '' !== bkery_template_visible_text( wp_strip_all_tags( $bkery_point_body ) ) ) : ?>
							<?php echo wp_kses_post( wpautop( $bkery_point_body ) ); ?>
						<?php endif; ?>
						<?php if ( array() !== $bkery_point_link ) : ?>
							<a class="text-link" href="<?php echo bkery_template_href_attr( $bkery_point_link['href'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"<?php echo $bkery_point_link['external'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $bkery_point_link['label'] ); ?></a>
						<?php endif; ?>
					</div>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>
