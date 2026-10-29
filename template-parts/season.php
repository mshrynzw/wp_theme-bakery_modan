<?php
/**
 * 季節限定のパン。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title = (string) bkery_get_theme_mod( 'bkery_season_title' );
$bkery_lead  = (string) bkery_get_theme_mod( 'bkery_season_lead' );
$bkery_posts = array();
if ( function_exists( 'bkery_get_breads_by_line' ) ) {
	$bkery_loaded = bkery_get_breads_by_line( 'seasonal' );
	if ( is_array( $bkery_loaded ) ) {
		$bkery_posts = $bkery_loaded;
	}
}

$bkery_items = array();
foreach ( $bkery_posts as $bkery_post ) {
	if ( is_object( $bkery_post ) && isset( $bkery_post->ID, $bkery_post->post_name ) && '' !== (string) $bkery_post->post_name ) {
		$bkery_items[] = $bkery_post;
	}
}
$bkery_total = count( $bkery_items );
?>
<section class="section season" id="season" data-section="season" aria-labelledby="season-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="season-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'season' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
		<?php if ( '' !== bkery_template_visible_text( $bkery_lead ) ) : ?>
			<p class="section__lead"><?php echo bkery_template_nl2br( $bkery_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>

	<div class="carousel" data-carousel="season" aria-roledescription="<?php esc_attr_e( 'カルーセル', 'bakery-s' ); ?>" aria-label="<?php echo esc_attr( $bkery_title ); ?>">
		<ul class="carousel__track season__track" tabindex="0" data-render="seasonal">
			<?php foreach ( $bkery_items as $bkery_index => $bkery_post ) : ?>
				<?php
				$bkery_thumb   = function_exists( 'get_post_thumbnail_id' ) ? (int) get_post_thumbnail_id( $bkery_post ) : 0;
				$bkery_name    = function_exists( 'get_the_title' ) ? (string) get_the_title( $bkery_post ) : (string) $bkery_post->post_title;
				$bkery_excerpt = function_exists( 'bkery_bread_card_excerpt' ) ? (string) bkery_bread_card_excerpt( $bkery_post ) : '';
				$bkery_price   = absint( get_post_meta( $bkery_post->ID, 'bkery_bread_price', true ) );
				echo bkery_template_season_card_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array(
						'slug'       => (string) $bkery_post->post_name,
						'name'       => $bkery_name,
						'excerpt'    => $bkery_excerpt,
						'price'      => $bkery_price,
						'image_html' => bkery_template_image_html( $bkery_thumb, 'bkery-card', false, $bkery_name ),
						'label'      => bkery_template_media_label( $bkery_thumb, (string) $bkery_post->post_name ),
						'index'      => $bkery_index + 1,
						'total'      => $bkery_total,
					)
				);
				?>
			<?php endforeach; ?>
		</ul>
		<div class="carousel__controls">
			<button class="round-btn" type="button" data-prev aria-label="<?php esc_attr_e( '前の商品', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<p class="carousel__count" data-count aria-live="polite"><?php if ( $bkery_total > 0 ) : ?><b>1</b> / <?php echo esc_html( (string) $bkery_total ); ?><?php endif; ?></p>
			<button class="round-btn" type="button" data-next aria-label="<?php esc_attr_e( '次の商品', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
	</div>
</section>
