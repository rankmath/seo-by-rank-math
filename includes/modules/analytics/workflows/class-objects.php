<?php
/**
 *  Install objects.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use Exception;
use RankMath\Helper;
use RankMath\Helpers\DB;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Schedule;

defined( 'ABSPATH' ) || exit;

/**
 * Objects class.
 */
class Objects extends Base {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$done = \boolval( get_option( 'rank_math_analytics_installed' ) );
		if ( $done ) {
			return;
		}

		$this->create_tables();
		$this->create_data_job();
		$this->flat_posts();

		update_option( 'rank_math_analytics_installed', true );
	}

	/**
	 * Create tables.
	 */
	public function create_tables() {
		DB::create_table(
			'rank_math_analytics_objects',
			'id bigint(20) unsigned NOT NULL auto_increment,
			created timestamp NOT NULL,
			title text NOT NULL,
			page varchar(500) NOT NULL,
			object_type varchar(100) NOT NULL,
			object_subtype varchar(100) NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			primary_key varchar(255) NOT NULL,
			seo_score tinyint NOT NULL default 0,
			page_score tinyint NOT NULL default 0,
			is_indexable tinyint(1) NOT NULL default 1,
			schemas_in_use varchar(500),
			desktop_interactive double default 0,
			desktop_pagescore double default 0,
			mobile_interactive double default 0,
			mobile_pagescore double default 0,
			pagespeed_refreshed timestamp,
			PRIMARY KEY  (id),
			KEY analytics_object_page (page(190))'
		);
	}

	/**
	 * Create jobs to fetch data.
	 */
	public function create_data_job() {
		// Clear old schedule.
		wp_clear_scheduled_hook( 'rank_math/analytics/get_analytics' );

		// Schedule new action only when there is no existing action.
		if ( false === as_next_scheduled_action( 'rank_math/analytics/data_fetch' ) ) {
			Helper::schedule_data_fetch();
		}
	}

	/**
	 * Flat posts
	 */
	public function flat_posts() {
		$ids = get_posts(
			[
				'post_type'      => $this->get_post_types(),
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			]
		);

		$counter = 0;
		$chunks  = \array_chunk( $ids, 50 );
		foreach ( $chunks as $chunk ) {
			++$counter;
			Schedule::single_action(
				time() + ( 60 * ( $counter / 2 ) ),
				'rank_math/analytics/flat_posts',
				[ $chunk ],
				'rank-math'
			);
		}

		// Check for posts.
		Schedule::single_action(
			time() + ( 60 * ( ( $counter + 1 ) / 2 ) ),
			'rank_math/analytics/flat_posts_completed',
			[],
			'rank-math'
		);

		// Clear cache.
		Workflow::add_clear_cache( time() + ( 60 * ( ( $counter + 2 ) / 2 ) ) );
	}

	/**
	 * Get post types to process.
	 */
	private function get_post_types() {
		$post_types = $this->do_filter( 'analytics/post_types', Helper::get_accessible_post_types() );
		unset( $post_types['attachment'] );
		if ( isset( $post_types['web-story'] ) ) {
			unset( $post_types['web-story'] );
		}

		return array_keys( $post_types );
	}
}
