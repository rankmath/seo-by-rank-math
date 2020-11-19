<?php
/**
 * The Analytics Module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Google\Console;

defined( 'ABSPATH' ) || exit;

/**
 * Watcher class.
 */
class Watcher {

	use Hooker;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Watcher
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Watcher ) ) {
			$instance = new Watcher();
			$instance->hooks();
		}

		return $instance;
	}

	/**
	 * Hooks
	 */
	public function hooks() {
		if ( Console::is_console_connected() ) {
			$this->action( 'save_post', 'update_post_info', 99 );
			$this->action( 'rank_math/schema/update', 'update_post_schema_info', 99 );
		}
	}

	/**
	 * Update post info for analytics.
	 *
	 * @param int $post_id Post id.
	 */
	public function update_post_info( $post_id ) {
		$status    = get_post_status( $post_id );
		$post_type = get_post_type( $post_id );
		if (
			'publish' !== $status ||
			wp_is_post_autosave( $post_id ) ||
			wp_is_post_revision( $post_id ) ||
			! Helper::is_post_type_accessible( $post_type )
		) {
			DB::objects()
				->where( 'object_type', 'post' )
				->where( 'object_id', $post_id )
				->delete();
			return;
		}

		$id      = get_post_meta( $post_id, 'rank_math_analytic_object_id', true );
		$fk      = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		$schemas = \RankMath\Schema\DB::get_schema_types( $post_id );

		if ( empty( $schemas ) && 'off' === get_post_meta( $post_id, 'rank_math_rich_snippet', true ) ) {
			$schemas = __( 'None', 'rank-math' );
		}

		// Update post.
		$id = DB::update_object(
			[
				'id'                  => $id,
				'created'             => get_the_modified_date( 'Y-m-d H:i:s', $post_id ),
				'title'               => get_the_title( $post_id ),
				'page'                => Stats::get_relative_url( get_permalink( $post_id ) ),
				'object_type'         => 'post',
				'object_subtype'      => $post_type,
				'object_id'           => $post_id,
				'primary_key'         => $fk,
				'seo_score'           => $fk ? get_post_meta( $post_id, 'rank_math_seo_score', true ) : 0,
				'is_indexable'        => Helper::is_post_indexable( $post_id ),
				'schemas_in_use'      => $schemas ? $schemas : '',
				'pagespeed_refreshed' => 'NULL',
			]
		);

		if ( $id > 0 ) {
			update_post_meta( $post_id, 'rank_math_analytic_object_id', $id );
		}
	}

	/**
	 * Update post info for analytics.
	 *
	 * @param int $post_id Post id.
	 */
	public function update_post_schema_info( $post_id ) {
		$id      = get_post_meta( $post_id, 'rank_math_analytic_object_id', true );
		$schemas = \RankMath\Schema\DB::get_schema_types( $post_id );

		// Update post.
		$id = DB::update_object(
			[
				'id'             => $id,
				'schemas_in_use' => $schemas ? $schemas : '',
			]
		);
	}
}
