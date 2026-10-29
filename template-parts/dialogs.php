<?php
/**
 * 商品詳細、写真拡大、動画、コピー結果のトースト。トップに 1 組だけ出す。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_frontend = array();
if ( function_exists( 'bkery_get_frontend_data' ) ) {
	$bkery_loaded = bkery_get_frontend_data();
	if ( is_array( $bkery_loaded ) ) {
		$bkery_frontend = $bkery_loaded;
	}
}

$bkery_video = bkery_template_media_url( $bkery_frontend, 'video' );
if ( bkery_template_should_show_video_placeholder( $bkery_video ) ) {
	$bkery_video_id = absint( bkery_get_theme_mod( 'bkery_video_id' ) );
	if ( $bkery_video_id > 0 && function_exists( 'wp_get_attachment_url' ) ) {
		$bkery_url = wp_get_attachment_url( $bkery_video_id );
		if ( is_string( $bkery_url ) ) {
			$bkery_video = $bkery_url;
		}
	}
}

$bkery_poster = bkery_template_media_url( $bkery_frontend, 'videoPoster' );
if ( '' === bkery_template_href( $bkery_poster ) ) {
	$bkery_poster_id = absint( bkery_get_theme_mod( 'bkery_video_poster_id' ) );
	if ( $bkery_poster_id > 0 && function_exists( 'wp_get_attachment_image_url' ) ) {
		$bkery_url = wp_get_attachment_image_url( $bkery_poster_id, 'full' );
		if ( is_string( $bkery_url ) ) {
			$bkery_poster = $bkery_url;
		}
	}
}

$bkery_video_title = (string) bkery_get_theme_mod( 'bkery_video_title' );
$bkery_video_sub   = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_video_sub' ) );
$bkery_show_empty  = bkery_template_should_show_video_placeholder( $bkery_video );
$bkery_poster_href = bkery_template_href( $bkery_poster );
?>
<dialog class="sheet" id="product-sheet" aria-labelledby="sheet-title">
	<div class="sheet__panel" data-sheet-panel>
		<div class="sheet__grip" data-sheet-grip><span></span></div>
		<button class="sheet__close round-btn" type="button" data-close aria-label="<?php esc_attr_e( '閉じる', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<div class="sheet__body" data-sheet-body></div>
	</div>
</dialog>

<dialog class="lightbox" id="lightbox" aria-label="<?php esc_attr_e( '写真の拡大表示', 'bakery-s' ); ?>">
	<div class="lightbox__inner">
		<button class="lightbox__close round-btn" type="button" data-close aria-label="<?php esc_attr_e( '閉じる', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<figure class="lightbox__figure">
			<img data-lightbox-img alt="">
			<figcaption data-lightbox-caption></figcaption>
		</figure>
		<button class="lightbox__nav lightbox__nav--prev round-btn" type="button" data-lightbox-prev aria-label="<?php esc_attr_e( '前の写真', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<button class="lightbox__nav lightbox__nav--next round-btn" type="button" data-lightbox-next aria-label="<?php esc_attr_e( '次の写真', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	</div>
</dialog>

<dialog class="sheet sheet--video" id="video-dialog" aria-labelledby="video-title">
	<div class="sheet__panel" data-sheet-panel>
		<div class="sheet__grip" data-sheet-grip><span></span></div>
		<button class="sheet__close round-btn" type="button" data-close aria-label="<?php esc_attr_e( '閉じる', 'bakery-s' ); ?>"><?php echo bkery_template_icon( 'i-close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<div class="sheet__body">
			<h2 class="sheet__title" id="video-title"><?php echo esc_html( $bkery_video_title ); ?></h2>
			<div class="video-frame" data-video-frame>
				<?php if ( $bkery_show_empty ) : ?>
					<?php
					$bkery_poster_style = '';
					if ( '' !== $bkery_poster_href ) {
						$bkery_poster_safe = esc_url( $bkery_poster_href );
						if ( is_string( $bkery_poster_safe ) && '' !== $bkery_poster_safe ) {
							$bkery_poster_style = ' style="--poster:url(' . $bkery_poster_safe . ')"';
						}
					}
					?>
					<div class="video-frame__empty"<?php echo $bkery_poster_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_url() ?>>
						<?php echo bkery_template_icon( 'i-play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<p><?php esc_html_e( '動画素材は準備中です', 'bakery-s' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $bkery_video_sub ) : ?>
				<p class="sheet__text"><?php echo esc_html( $bkery_video_sub ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</dialog>

<div class="toast" data-toast role="status" aria-live="polite"></div>
