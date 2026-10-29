<?php
/**
 * トップページ。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">
<?php
get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/concept' );
get_template_part( 'template-parts/gallery' );
get_template_part( 'template-parts/season' );
get_template_part( 'template-parts/menu' );
get_template_part( 'template-parts/bake' );
get_template_part( 'template-parts/owner' );
get_template_part( 'template-parts/shop' );
get_template_part( 'template-parts/instagram' );
?>
</main>
<?php
get_footer();
