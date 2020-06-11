<?php
/**
 * Methods for frontend and backend in admin-only module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Search_Console class.
 */
class Search_Console_Common {

	use Hooker;

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		if ( Helper::has_cap( 'search_console' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}
		$this->action( 'rank_math/search_console/get_analytics', 'add_day_crawler' );
	}

	/**
	 * Add admin bar item.
	 *
	 * @param Admin_Bar_Menu $menu Menu class instance.
	 */
	public function admin_bar_items( $menu ) {
		$menu->add_sub_menu(
			'search-console',
			[
				'title'    => esc_html__( 'Search Console', 'rank-math' ),
				'href'     => Helper::get_admin_url( 'search-console' ),
				'meta'     => [ 'title' => esc_html__( 'Review analytics and sitemaps', 'rank-math' ) ],
				'priority' => 50,
			]
		);
	}

	/**
	 * CRON Job.
	 */
	public function add_day_crawler() {
		$crawler = Data_Fetcher::get();
		$start   = Helper::get_midnight( time() - DAY_IN_SECONDS );

		$crawler->push_to_queue( date_i18n( 'Y-m-d', $start - ( DAY_IN_SECONDS * 2 ) ) );
		$crawler->save()->dispatch();
	}
}
