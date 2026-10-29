<?php
/**
 * フォールバックテンプレート。
 *
 * トップの見た目は front-page.php が担当する。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'コンテンツが見つかりませんでした。', 'bakery-s' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
