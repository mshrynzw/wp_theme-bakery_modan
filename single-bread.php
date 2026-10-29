<?php
/**
 * パンの個別ページ。ダイアログのシートは付けない。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

get_header();

$bkery_frontend = array();
if ( function_exists( 'bkery_get_frontend_data' ) ) {
	$bkery_loaded = bkery_get_frontend_data();
	if ( is_array( $bkery_loaded ) ) {
		$bkery_frontend = $bkery_loaded;
	}
}
$bkery_schedule = bkery_template_bake_schedule( $bkery_frontend );
if ( array() === $bkery_schedule && function_exists( 'bkery_get_bake_schedule' ) ) {
	$bkery_loaded_schedule = bkery_get_bake_schedule();
	$bkery_schedule        = bkery_template_bake_schedule(
		array(
			'bakeSchedule' => is_array( $bkery_loaded_schedule ) ? $bkery_loaded_schedule : array(),
		)
	);
}

$bkery_cafe_open  = (string) bkery_get_theme_mod( 'bkery_shop_cafe_open' );
$bkery_cafe_close = (string) bkery_get_theme_mod( 'bkery_shop_cafe_close' );
$bkery_links      = bkery_template_rail_links();
?>
<main id="main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php
			the_post();
			$bkery_line     = (string) get_post_meta( get_the_ID(), 'bkery_bread_line', true );
			$bkery_seasonal = bkery_template_is_seasonal( $bkery_line );
			$bkery_section  = $bkery_seasonal ? 'season' : 'menu';
			$bkery_terms    = function_exists( 'get_the_terms' ) ? get_the_terms( get_the_ID(), 'bread_category' ) : array();
			if ( ! is_array( $bkery_terms ) ) {
				$bkery_terms = array();
			}
			$bkery_category = bkery_template_primary_category_slug( $bkery_terms );
			$bkery_thumb    = function_exists( 'get_post_thumbnail_id' ) ? (int) get_post_thumbnail_id() : 0;
			$bkery_name     = get_the_title();
			$bkery_price    = absint( get_post_meta( get_the_ID(), 'bkery_bread_price', true ) );
			$bkery_price_html = bkery_template_price_html( $bkery_price );
			$bkery_times    = bkery_template_product_bake_times(
				$bkery_schedule,
				(string) get_post_field( 'post_name', get_the_ID() ),
				(string) get_post_meta( get_the_ID(), 'bkery_bread_bake_times', true )
			);
			$bkery_show_cafe = array() === $bkery_times
				&& 'drink' === $bkery_category
				&& bkery_template_clock_is_valid( $bkery_cafe_open )
				&& bkery_template_clock_is_valid( $bkery_cafe_close );
			$bkery_shop_href = bkery_template_href_attr( bkery_template_current_anchor( 'shop' ) );
			$bkery_bake_href = bkery_template_href_attr( bkery_template_current_anchor( 'bake' ) );
			$bkery_line_href = bkery_template_href_attr( bkery_template_current_anchor( $bkery_section ) );
			$bkery_line_label = isset( $bkery_links[ $bkery_section ] ) ? bkery_template_translate( $bkery_links[ $bkery_section ] ) : '';
			?>
			<article <?php post_class( 'section' ); ?> data-section="<?php echo esc_attr( $bkery_section ); ?>">
				<figure class="ph sheet__media" data-label="<?php echo esc_attr( bkery_template_media_label( $bkery_thumb, (string) get_post_field( 'post_name', get_the_ID() ) ) ); ?>">
					<?php echo bkery_template_image_html( $bkery_thumb, 'bkery-card', false, $bkery_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</figure>
				<div class="sheet__meta">
					<?php if ( $bkery_seasonal ) : ?>
						<span class="tag tag--season"><?php echo bkery_template_icon( 'i-leaf', 'icon', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( bkery_template_translate( '期間限定' ) ); ?></span>
					<?php else : ?>
						<span class="tag tag--regular"><?php echo esc_html( bkery_template_translate( '定番' ) ); ?></span>
					<?php endif; ?>
				</div>
				<h1 class="sheet__title"><?php echo esc_html( $bkery_name ); ?></h1>
				<?php if ( '' !== $bkery_price_html ) : ?>
					<p class="price"><?php echo $bkery_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) get_post_field( 'post_content', get_the_ID() ) ) ) : ?>
					<div class="prose">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
				<?php if ( array() !== $bkery_times ) : ?>
					<div class="sheet__bake">
						<p class="sheet__bake-title"><?php echo bkery_template_icon( 'i-clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( '今日の焼き上がり', 'bakery-s' ); ?></p>
						<p class="sheet__bake-times">
							<?php foreach ( $bkery_times as $bkery_time ) : ?>
								<span><?php echo esc_html( $bkery_time ); ?></span>
							<?php endforeach; ?>
						</p>
					</div>
				<?php elseif ( $bkery_show_cafe ) : ?>
					<div class="sheet__bake">
						<p class="sheet__bake-title"><?php echo bkery_template_icon( 'i-cup' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'イートイン', 'bakery-s' ); ?></p>
						<p class="sheet__bake-text"><?php echo esc_html( bkery_template_cafe_hours_sentence( $bkery_cafe_open, $bkery_cafe_close ) ); ?></p>
					</div>
				<?php endif; ?>
				<div class="btn-row">
					<a class="btn btn--solid" href="<?php echo $bkery_shop_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'アクセスを見る', 'bakery-s' ); ?></a>
					<a class="btn btn--line" href="<?php echo $bkery_bake_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( 'i-clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( '焼き上がり時間', 'bakery-s' ); ?></a>
					<?php if ( '' !== $bkery_line_label ) : ?>
						<a class="btn btn--line" href="<?php echo $bkery_line_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo bkery_template_icon( bkery_template_section_icon( $bkery_section ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_line_label ); ?></a>
					<?php endif; ?>
				</div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'コンテンツが見つかりませんでした。', 'bakery-s' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
