<?php
/**
 * PHPUnit bootstrap for WordPress-free unit tests.
 *
 * @package bakery-s
 */

declare(strict_types=1);

$theme_dir = dirname(__DIR__, 3);

defined('ABSPATH') || define('ABSPATH', $theme_dir . '/');

require_once $theme_dir . '/inc/shop-hours.php';
require_once $theme_dir . '/inc/theme-mods.php';
require_once $theme_dir . '/inc/colors.php';
