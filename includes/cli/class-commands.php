<?php
/**
 * Rank Math core CLI commands.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\WP_CLI
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\CLI;

use WP_CLI;
use WP_CLI_Command;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Commands class.
 */
class Commands extends WP_CLI_Command {

	/**
	 * Generate the sitemap.
	 *
	 * @param array $args Arguments passed.
	 */
	public static function sitemap_generate( $args ) {
		$sitemap = Helper::get_module( 'sitemap' );
		if ( false === $sitemap ) {
			WP_CLI::error( 'Sitemap module not active.' );
			return;
		}

		( new Sitemap_WPCLI() )->cache_sitemaps();
	}
}
