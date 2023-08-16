<?php
/**
 * WordPress Helpers
 *
 * @package      MyThemeShop\Helpers
 * @copyright    Copyright (C) 2018, MyThemeShop - info@mythemeshop.com
 * @link         http://mythemeshop.com
 * @since        1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Helpers
 * Version:           1.1.22
 * Plugin URI:        http://mythemeshop.com/wordpress-helpers/
 * Description:       Collection of utilities required during development of a plugin or theme for WordPress. Built for developers by developers.
 * Author:            MyThemeShop
 * Author URI:        http://mythemeshop.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Tested up to:      5.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloading.
 */
include dirname( __FILE__ ) . '/vendor/autoload.php';
