<?php
/**
 * 店内のようすと製造動画。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title = (string) bkery_get_theme_mod( 'bkery_gallery_title' );
$bkery_lead  = (string) bkery_get_theme_mod( 'bkery_gallery_lead' );
$bkery_count = function_exists( 'bkery_slot_counts' ) ? (int) bkery_slot_counts()['gallery'] : 6;
$bkery_slots = array();
for ( $bkery_index = 1; $bkery_index <= $bkery_count; $bkery_index++ ) {
	$bkery_image_id = absint( bkery_get_theme_mod( 'bkery_gallery_image_' . $bkery_index ) );
	$bkery_slots[]  = array(
		'image_id' => $bkery_image_id,
		'caption'  => (string) bkery_get_theme_mod( 'bkery_gallery_caption_' . $bkery_index ),
		'label'    => bkery_template_media_label( $bkery_image_id, 'gallery-' . $bkery_index ),
	);
}
$bkery_slots       = bkery_template_visible_gallery_slots( $bkery_slots );
$bkery_video_title = (string) bkery_get_theme_mod( 'bkery_video_title' );
$bkery_video_sub   = (string) bkery_get_theme_mod( 'bkery_video_sub' );
$bkery_poster_id   = absint( bkery_get_theme_mod( 'bkery_video_poster_id' ) );
$bkery_poster      = bkery_template_image_html( $bkery_poster_id, 'bkery-gallery' );
$bkery_poster_label = bkery_template_media_label( $bkery_poster_id, 'bread-making-video-poster' );
?>
<section class="section section--kraft gallery" id="gallery" data-section="gallery" aria-labelledby="gallery-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="gallery-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'gallery' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
		<?php if ( '' !== bkery_template_visible_text( $bkery_lead ) ) : ?>
			<p class="section__lead"><?php echo bkery_template_nl2br( $bkery_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>

	<?php if ( array() !== $bkery_slots ) : ?>
		<div class="carousel" data-carousel="gallery" aria-roledescription="<?php esc_attr_e( 'カルーセル', 'bakery-s' ); ?>" aria-label="<?php esc_attr_e( '店内の写真', 'bakery-s' ); ?>">
			<ul class="carousel__track gallery__track" tabindex="0">
				<?php foreach ( $bkery_slots as $bkery_index => $bkery_slot ) : ?>
					<?php
					$bkery_caption = $bkery_slot['caption'];
					$bkery_zoom    = '' !== $bkery_caption
						? sprintf( bkery_template_translate( '写真を拡大：%s' ), $bkery_caption )
						: bkery_template_translate( '写真を拡大' );
					$bkery_image   = bkery_template_image_html( $bkery_slot['image_id'], 'bkery-gallery', false, '' !== $bkery_caption ? $bkery_caption : null );
					?>
					<li class="carousel__slide gallery__slide">
						<button class="gallery__item" type="button" data-lightbox="<?php echo esc_attr( (string) $bkery_index ); ?>" aria-label="<?php echo esc_attr( $bkery_zoom ); ?>">
							<span class="ph" data-label="<?php echo esc_attr( $bkery_slot['label'] ); ?>"><?php echo $bkery_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( '' !== $bkery_caption ) : ?>
								<span class="gallery__caption"><?php echo esc_html( $bkery_caption ); ?></span>
							<?php endif; ?>
							<?php echo bkery_template_icon( 'i-expand', 'icon gallery__zoom' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="carousel__controls">
				<button class="round-btn" type="button" data-prev aria-label="<?php esc_attr_e( '前の写真', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<div class="carousel__dots" data-dots></div>
				<button class="round-btn" type="button" data-next aria-label="<?php esc_attr_e( '次の写真', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
		</div>
	<?php endif; ?>

	<button class="video-card" type="button" data-video-open aria-haspopup="dialog">
		<span class="ph video-card__poster" data-label="<?php echo esc_attr( $bkery_poster_label ); ?>">
			<?php echo $bkery_poster; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
		<span class="video-card__play"><?php echo bkery_template_icon( 'i-play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="video-card__text">
			<?php if ( '' !== bkery_template_visible_text( $bkery_video_title ) ) : ?>
				<span class="video-card__title"><?php echo esc_html( $bkery_video_title ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== bkery_template_visible_text( $bkery_video_sub ) ) : ?>
				<span class="video-card__sub"><?php echo esc_html( $bkery_video_sub ); ?></span>
			<?php endif; ?>
		</span>
	</button>
</section>
