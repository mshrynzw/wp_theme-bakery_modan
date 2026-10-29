<?php
/**
 * いつものパン。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

$bkery_title = (string) bkery_get_theme_mod( 'bkery_menu_title' );
$bkery_lead  = (string) bkery_get_theme_mod( 'bkery_menu_lead' );
$bkery_posts = array();
if ( function_exists( 'bkery_get_breads_by_line' ) ) {
	$bkery_loaded = bkery_get_breads_by_line( 'regular' );
	if ( is_array( $bkery_loaded ) ) {
		$bkery_posts = $bkery_loaded;
	}
}
$bkery_terms = array();
if ( function_exists( 'bkery_get_bread_categories' ) ) {
	$bkery_loaded_terms = bkery_get_bread_categories();
	if ( is_array( $bkery_loaded_terms ) ) {
		$bkery_terms = $bkery_loaded_terms;
	}
}

$bkery_items = array();
$bkery_slugs = array();
foreach ( $bkery_posts as $bkery_post ) {
	if ( ! is_object( $bkery_post ) || ! isset( $bkery_post->ID, $bkery_post->post_name ) || '' === (string) $bkery_post->post_name ) {
		continue;
	}
	$bkery_items[] = $bkery_post;
	$bkery_terms_for_post = function_exists( 'get_the_terms' ) ? get_the_terms( $bkery_post->ID, 'bread_category' ) : array();
	if ( ! is_array( $bkery_terms_for_post ) ) {
		$bkery_terms_for_post = array();
	}
	$bkery_slugs[] = bkery_template_primary_category_slug( $bkery_terms_for_post );
}
$bkery_counts = bkery_template_menu_counts( count( $bkery_items ), bkery_template_count_by_slug( $bkery_slugs ) );
?>
<section class="section section--kraft menu" id="menu" data-section="menu" aria-labelledby="menu-title">
	<div class="section__head" data-reveal>
		<h2 class="section__title" id="menu-title"><?php echo bkery_template_icon( bkery_template_section_icon( 'menu' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $bkery_title ); ?></h2>
		<?php if ( '' !== bkery_template_visible_text( $bkery_lead ) ) : ?>
			<p class="section__lead"><?php echo bkery_template_nl2br( $bkery_lead ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>

	<div class="tabs" role="group" aria-label="<?php esc_attr_e( 'カテゴリで絞り込む', 'bakery-s' ); ?>" data-menu-tabs>
		<?php echo bkery_template_menu_tab_html( 'all', bkery_template_translate( 'すべて' ), (int) $bkery_counts['all'], true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php foreach ( $bkery_terms as $bkery_term ) : ?>
			<?php
			if ( ! is_object( $bkery_term ) || ! isset( $bkery_term->slug, $bkery_term->name ) ) {
				continue;
			}
			$bkery_slug = (string) $bkery_term->slug;
			if ( '' === $bkery_slug || 'all' === $bkery_slug ) {
				continue;
			}
			$bkery_count = isset( $bkery_counts[ $bkery_slug ] ) ? (int) $bkery_counts[ $bkery_slug ] : 0;
			echo bkery_template_menu_tab_html( $bkery_slug, (string) $bkery_term->name, $bkery_count, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		<?php endforeach; ?>
	</div>
	<ul class="menu__grid" data-render="menu" aria-live="polite">
		<?php foreach ( $bkery_items as $bkery_post ) : ?>
			<?php
			$bkery_terms_for_post = function_exists( 'get_the_terms' ) ? get_the_terms( $bkery_post->ID, 'bread_category' ) : array();
			if ( ! is_array( $bkery_terms_for_post ) ) {
				$bkery_terms_for_post = array();
			}
			$bkery_thumb   = function_exists( 'get_post_thumbnail_id' ) ? (int) get_post_thumbnail_id( $bkery_post ) : 0;
			$bkery_name    = function_exists( 'get_the_title' ) ? (string) get_the_title( $bkery_post ) : (string) $bkery_post->post_title;
			$bkery_price   = absint( get_post_meta( $bkery_post->ID, 'bkery_bread_price', true ) );
			echo bkery_template_menu_card_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'slug'       => (string) $bkery_post->post_name,
					'name'       => $bkery_name,
					'category'   => bkery_template_primary_category_slug( $bkery_terms_for_post ),
					'price'      => $bkery_price,
					'image_html' => bkery_template_image_html( $bkery_thumb, 'bkery-card', false, $bkery_name ),
					'label'      => bkery_template_media_label( $bkery_thumb, (string) $bkery_post->post_name ),
				)
			);
			?>
		<?php endforeach; ?>
	</ul>
</section>
