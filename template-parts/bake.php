<?php
/**
 * 焼き上がり時間。時刻の並びは bkery_get_frontend_data() の順のまま出す。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title = (string) bkery_get_theme_mod( 'bkery_bake_title' );
$bkery_lead  = (string) bkery_get_theme_mod( 'bkery_bake_lead' );
$bkery_note  = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_bake_note' ) );
$bkery_calendar_label = bkery_template_visible_text( (string) bkery_get_theme_mod( 'bkery_bake_calendar_label' ) );
$bkery_calendar       = bkery_template_href_attr( (string) bkery_get_theme_mod( 'bkery_link_calendar' ) );

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

$bkery_products = array();
$bkery_groups   = array(
	'regular'  => array(),
	'seasonal' => array(),
);
if ( function_exists( 'bkery_get_breads_by_line' ) ) {
	$bkery_regular = bkery_get_breads_by_line( 'regular' );
	$bkery_season  = bkery_get_breads_by_line( 'seasonal' );
	$bkery_groups['regular']  = is_array( $bkery_regular ) ? $bkery_regular : array();
	$bkery_groups['seasonal'] = is_array( $bkery_season ) ? $bkery_season : array();
}
foreach ( $bkery_groups as $bkery_group_line => $bkery_group_posts ) {
	foreach ( $bkery_group_posts as $bkery_post ) {
		if ( ! is_object( $bkery_post ) || ! isset( $bkery_post->ID, $bkery_post->post_name ) || '' === (string) $bkery_post->post_name ) {
			continue;
		}
		$bkery_line = (string) get_post_meta( $bkery_post->ID, 'bkery_bread_line', true );
		if ( '' === $bkery_line ) {
			$bkery_line = (string) $bkery_group_line;
		}
		$bkery_name = function_exists( 'get_the_title' ) ? (string) get_the_title( $bkery_post ) : (string) $bkery_post->post_title;
		$bkery_products[ (string) $bkery_post->post_name ] = array(
			'name'     => $bkery_name,
			'seasonal' => bkery_template_is_seasonal( $bkery_line ),
		);
	}
}
?>
<section class="section bake" id="bake" data-section="bake" aria-labelledby="bake-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="bake-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'bake' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
		<?php if ( '' !== bkery_template_visible_text( $bkery_lead ) ) : ?>
			<p class="section__lead"><?php echo bkery_template_nl2br( $bkery_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>

	<div class="bake__next" data-bake-next aria-live="polite"></div>
	<ol class="bake__list" data-render="bake">
		<?php foreach ( $bkery_schedule as $bkery_slot ) : ?>
			<?php $bkery_chips = bkery_template_bake_chips( $bkery_slot['items'], $bkery_products ); ?>
			<li class="bake__item" data-bake-slot="<?php echo esc_attr( $bkery_slot['time'] ); ?>">
				<span class="bake__time"><?php echo esc_html( $bkery_slot['time'] ); ?></span>
				<?php if ( array() !== $bkery_chips ) : ?>
					<div class="chips">
						<?php foreach ( $bkery_chips as $bkery_chip ) : ?>
							<?php echo bkery_template_chip_html( $bkery_chip ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php if ( '' !== $bkery_note ) : ?>
		<p class="note"><?php echo bkery_template_nl2br( $bkery_note ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>

	<?php if ( '' !== $bkery_calendar && '' !== $bkery_calendar_label ) : ?>
		<a class="btn btn--line" href="<?php echo $bkery_calendar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-link="calendar"<?php echo bkery_template_is_external_href( $bkery_calendar ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo bkery_template_icon( 'i-clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_calendar_label ); ?><?php echo bkery_template_icon( 'i-out', 'icon icon--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<?php endif; ?>
</section>
