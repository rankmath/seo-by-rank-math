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
use RankMath\Sitemap\Cache;
use RankMath\Sitemap\Sitemap_XML;

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
	public function sitemap_generate( $args ) {
		$sitemap = Helper::get_module( 'sitemap' );
		if ( false === $sitemap ) {
			WP_CLI::error( 'Sitemap module not active.' );
			return;
		}

		Cache::invalidate_storage();
		$generator = new Sitemap_XML( '1' );
		$generator->get_output();

		WP_CLI::success( 'Sitemap generated.' );
	}
}
