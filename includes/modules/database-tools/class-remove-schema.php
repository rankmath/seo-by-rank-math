<?php
/**
 * The Schema Remover tool to remove the meta data from the old format (<1.0.48).
 *
 * @since      1.0.65
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;
use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Remove_Schema class.
 */
class Remove_Schema extends \WP_Background_Process {

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'delete_old_schema';

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Remove_Schema
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Remove_Schema ) ) {
			$instance = new Remove_Schema();
		}

		return $instance;
	}

	/**
	 * Start creating batches.
	 *
	 * @param array $meta_ids Posts to process.
	 */
	public function start( $meta_ids ) {
		$chunks = array_chunk( $meta_ids, 100 );
		foreach ( $chunks as $chunk ) {
			$this->push_to_queue( $chunk );
		}

		$this->save()->dispatch();
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$meta_ids = get_option( 'rank_math_old_schema_data' );
		delete_option( 'rank_math_old_schema_data' );
		Helper::add_notification(
			sprintf( 'Rank Math: Deleted %d schema data successfully.', count( $meta_ids ) ),
			[
				'type'    => 'success',
				'id'      => 'rank_math_remove_old_schema_data',
				'classes' => 'rank-math-notice',
			]
		);

		parent::complete();
	}

	/**
	 * Task to perform
	 *
	 * @param array $meta_ids Posts to process.
	 *
	 * @return bool
	 */
	protected function task( $meta_ids ) {
		try {
			foreach ( $meta_ids as $meta_id ) {
				delete_metadata_by_mid( 'post', $meta_id );
			}
			return false;
		} catch ( \Exception $error ) {
			return true;
		}
	}

	/**
	 * Find posts with old schema data.
	 *
	 * @return array
	 */
	public function find() {
		$meta_ids = get_option( 'rank_math_old_schema_data' );
		if ( false !== $meta_ids ) {
			return $meta_ids;
		}

		// Schema Metadata.
		$meta_ids = Database::table( 'postmeta' )
			->distinct()
			->select( 'meta_id' )
			->whereLike( 'meta_key', 'rank_math_snippet', '' )
			->get();

		$meta_ids = wp_list_pluck( $meta_ids, 'meta_id' );
		update_option( 'rank_math_old_schema_data', $meta_ids );

		return $meta_ids;
	}
}
