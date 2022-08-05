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
use RankMath\Google\Authentication;

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
		if ( Authentication::is_authorized() ) {
			$this->action( 'save_post', 'update_post_info', 99 );
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

		// Get primary focus keyword.
		$primary_keyword = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		if ( $primary_keyword ) {
			$primary_keyword = explode( ',', $primary_keyword );
			$primary_keyword = trim( $primary_keyword[0] );
		}

		// Set argument for object row.
		$object_args = [
			'id'                  => get_post_meta( $post_id, 'rank_math_analytic_object_id', true ),
			'created'             => get_the_modified_date( 'Y-m-d H:i:s', $post_id ),
			'title'               => get_the_title( $post_id ),
			'page'                => Stats::get_relative_url( urldecode( get_permalink( $post_id ) ) ),
			'object_type'         => 'post',
			'object_subtype'      => $post_type,
			'object_id'           => $post_id,
			'primary_key'         => $primary_keyword,
			'seo_score'           => $primary_keyword ? get_post_meta( $post_id, 'rank_math_seo_score', true ) : 0,
			'schemas_in_use'      => \RankMath\Schema\DB::get_schema_types( $post_id, true, false ),
			'is_indexable'        => Helper::is_post_indexable( $post_id ),
			'pagespeed_refreshed' => 'NULL',
		];

		// Get translated object info in case multi-language plugin is installed.
		$translated_objects = apply_filters( 'rank_math/analytics/get_translated_objects', $post_id );
		if ( false !== $translated_objects && is_array( $translated_objects ) ) {
			// Remove current object info from objects table.
			DB::objects()
				->where( 'object_id', $post_id )
				->delete();

			foreach ( $translated_objects as $obj ) {
				$object_args['title'] = $obj['title'];
				$object_args['page']  = $obj['url'];

				DB::add_object( $object_args );
			}

			// Here we don't need to add `rank_math_analytic_object_id` post meta, because we always remove old translated objects info and add new one, in case of multi-lanauge.
			return;
		}

		// Update post from objects table.
		$id = DB::update_object( $object_args );

		if ( $id > 0 ) {
			update_post_meta( $post_id, 'rank_math_analytic_object_id', $id );
		}
	}
}
