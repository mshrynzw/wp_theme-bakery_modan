<?php
/**
 * ヘッダー。スキップリンク、SVG スプライト、背景ステージ、目次。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( '/template-parts/markup.php' );

$bkery_theme_color = (string) bkery_get_theme_mod( 'bkery_color_ink' );
?><!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php if ( '' !== $bkery_theme_color ) : ?>
<meta name="theme-color" content="<?php echo esc_attr( $bkery_theme_color ); ?>">
<?php endif; ?>
<?php // 描画前に js クラスが要る。設計書 4.2 が許可する 1 行。外部 script src は置かない。 ?>
<script>document.documentElement.classList.replace('no-js', 'js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<svg class="sprite" aria-hidden="true" focusable="false">
  <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.3" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="13" r="7.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 9.5V13l2.5 1.8M4.5 5.5 7 3.5M19.5 5.5 17 3.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></symbol>
  <symbol id="i-insta" viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3.8" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="17" cy="7" r="1.1" fill="currentColor"/></symbol>
  <symbol id="i-loaf" viewBox="0 0 24 24"><path d="M4 11.5C4 7.9 7.6 6 12 6s8 1.9 8 5.5c0 1-.6 1.6-1.5 1.8V18a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1v-4.7C4.6 13.1 4 12.5 4 11.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9.5l-1 2M12.5 9.2l-1 2M16 9.5l-1 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></symbol>
  <symbol id="i-leaf" viewBox="0 0 24 24"><path d="M5 19c0-8 5-13 14-14-.5 9-5.5 14-13 14H5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M5 19c3-4 6-7 10-9.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></symbol>
  <symbol id="i-wheat" viewBox="0 0 24 24"><path d="M12 21V8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M12 8c-2.4-.3-3.6-1.8-3.6-4 2.3.2 3.6 1.6 3.6 4Zm0 0c2.4-.3 3.6-1.8 3.6-4-2.3.2-3.6 1.6-3.6 4Zm0 5c-2.4-.3-3.6-1.8-3.6-4 2.3.2 3.6 1.6 3.6 4Zm0 0c2.4-.3 3.6-1.8 3.6-4-2.3.2-3.6 1.6-3.6 4Zm0 5c-2.4-.3-3.6-1.8-3.6-4 2.3.2 3.6 1.6 3.6 4Zm0 0c2.4-.3 3.6-1.8 3.6-4-2.3.2-3.6 1.6-3.6 4Z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></symbol>
  <symbol id="i-cup" viewBox="0 0 24 24"><path d="M4.5 9h12v5a5 5 0 0 1-5 5h-2a5 5 0 0 1-5-5V9Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M16.5 10.5h1.2a2.3 2.3 0 0 1 0 4.6h-1.5M8 3.5c-.8 1 .8 2-.1 3M11.5 3.5c-.8 1 .8 2-.1 3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></symbol>
  <symbol id="i-play" viewBox="0 0 24 24"><path d="M8 5.5v13l10.5-6.5L8 5.5Z" fill="currentColor"/></symbol>
  <symbol id="i-prev" viewBox="0 0 24 24"><path d="M14.5 5.5 8 12l6.5 6.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-next" viewBox="0 0 24 24"><path d="M9.5 5.5 16 12l-6.5 6.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-close" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-copy" viewBox="0 0 24 24"><rect x="8.5" y="8.5" width="11" height="11" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M15.5 8.5V6a1.5 1.5 0 0 0-1.5-1.5H6A1.5 1.5 0 0 0 4.5 6v8A1.5 1.5 0 0 0 6 15.5h2.5" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
  <symbol id="i-out" viewBox="0 0 24 24"><path d="M13.5 5.5h5v5M18.5 5.5 11 13M10 6.5H7a1.5 1.5 0 0 0-1.5 1.5v9A1.5 1.5 0 0 0 7 18.5h9a1.5 1.5 0 0 0 1.5-1.5v-3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-expand" viewBox="0 0 24 24"><path d="M14 4.5h5.5V10M10 19.5H4.5V14M19.5 4.5 13.5 10.5M4.5 19.5l6-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></symbol>
</svg>
<a class="skip-link" href="#main"><?php esc_html_e( '本文へスキップ', 'bakery-s' ); ?></a>
<?php get_template_part( 'template-parts/stage' ); ?>
<?php get_template_part( 'template-parts/rail' ); ?>
<div class="site">
