<?php
/**
 * メインビジュアル。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_name    = (string) get_bloginfo( 'name' );
$bkery_tagline = bkery_template_visible_text( (string) get_bloginfo( 'description' ) );
$bkery_open    = (string) bkery_get_theme_mod( 'bkery_shop_open' );
$bkery_close   = (string) bkery_get_theme_mod( 'bkery_shop_close' );
$bkery_label   = bkery_template_hours_status_label( $bkery_open, $bkery_close );
$bkery_catch   = (string) bkery_get_theme_mod( 'bkery_hero_catch' );
$bkery_lead    = (string) bkery_get_theme_mod( 'bkery_hero_lead' );
$bkery_count   = function_exists( 'bkery_slot_counts' ) ? (int) bkery_slot_counts()['hero'] : 3;
$bkery_shop    = bkery_template_href_attr( bkery_template_current_anchor( 'shop' ) );
$bkery_bake    = bkery_template_href_attr( bkery_template_current_anchor( 'bake' ) );
$bkery_insta   = bkery_template_href_attr( bkery_template_current_anchor( 'instagram' ) );
?>
<section class="hero" id="hero" data-section="hero" aria-label="<?php esc_attr_e( 'トップ', 'bakery-s' ); ?>">
	<div class="hero__top">
		<h1 class="logo">
			<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php endif; ?>
			<span class="logo__en"><?php echo esc_html( $bkery_name ); ?></span>
			<?php if ( '' !== $bkery_tagline ) : ?>
				<span class="logo__ja"><?php echo esc_html( $bkery_tagline ); ?></span>
			<?php endif; ?>
		</h1>
		<p class="status-pill" data-status-pill>
			<span class="status-dot" data-status-dot></span>
			<span data-status-label><?php echo esc_html( $bkery_label ); ?></span>
		</p>
	</div>

	<div class="hero__visual">
		<div class="hero__door" data-hero>
			<?php for ( $bkery_index = 1; $bkery_index <= $bkery_count; $bkery_index++ ) : ?>
				<?php
				$bkery_image_id = absint( bkery_get_theme_mod( 'bkery_hero_slide_' . $bkery_index ) );
				$bkery_slide_label = bkery_template_media_label( $bkery_image_id, 'hero-' . $bkery_index );
				$bkery_class       = 'hero__slide ph' . ( 1 === $bkery_index ? ' is-active' : '' );
				$bkery_image       = bkery_template_image_html( $bkery_image_id, 'bkery-hero', 1 === $bkery_index );
				?>
				<figure class="<?php echo esc_attr( $bkery_class ); ?>" data-label="<?php echo esc_attr( $bkery_slide_label ); ?>">
					<?php echo $bkery_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() ?>
				</figure>
			<?php endfor; ?>
		</div>
		<?php if ( '' !== bkery_template_visible_text( $bkery_catch ) ) : ?>
			<p class="hero__catch"><?php echo bkery_template_nl2br( $bkery_catch ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
		<div class="hero__dots" data-hero-dots role="group" aria-label="<?php esc_attr_e( 'メインビジュアルの写真を選ぶ', 'bakery-s' ); ?>"></div>
	</div>

	<?php if ( '' !== bkery_template_visible_text( $bkery_lead ) ) : ?>
		<p class="hero__lead"><?php echo bkery_template_nl2br( $bkery_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>

	<ul class="quick" aria-label="<?php esc_attr_e( 'よく見られている情報', 'bakery-s' ); ?>">
		<li><a class="quick__link" href="<?php echo $bkery_shop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'アクセス', 'bakery-s' ); ?><small><?php esc_html_e( '地図と営業時間', 'bakery-s' ); ?></small></span></a></li>
		<li><a class="quick__link" href="<?php echo $bkery_bake; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( '焼き上がり', 'bakery-s' ); ?><small data-next-bake-quick><?php esc_html_e( '今日の時間を見る', 'bakery-s' ); ?></small></span></a></li>
		<li><a class="quick__link" href="<?php echo $bkery_insta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-insta' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Instagram', 'bakery-s' ); ?><small><?php esc_html_e( '新作をチェック', 'bakery-s' ); ?></small></span></a></li>
	</ul>
</section>
