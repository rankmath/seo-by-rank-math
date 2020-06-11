<?php
/**
 * The DB interface.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Links
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Links;

defined( 'ABSPATH' ) || exit;

/**
 * Storage class.
 */
class Storage {

	/**
	 * Get query builder.
	 *
	 * @return \MyThemeShop\Database\Query_Builder
	 */
	private function table() {
		return \MyThemeShop\Helpers\DB::query_builder( 'rank_math_internal_links' );
	}

	/**
	 * Removes all data for a given post.
	 *
	 * @param  int $post_id The post ID to remove the records for.
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function cleanup( $post_id ) {
		return $this->table()->where( 'post_id', $post_id )->delete();
	}

	/**
	 * Get array of links from the database for given post.
	 *
	 * @param int $post_id The post to get the links for.
	 *
	 * @return Link[] The links array.
	 */
	public function get_links( $post_id ) {
		$links   = [];
		$results = $this->table()
			->select( [ 'url', 'post_id', 'target_post_id', 'type' ] )
			->where( 'post_id', $post_id )
			->get();

		foreach ( $results as $link ) {
			$links[] = new Link( $link->url, $link->target_post_id, $link->type );
		}

		return $links;
	}

	/**
	 * Save links for a post.
	 *
	 * @param integer $post_id The post ID to save.
	 * @param Link[]  $links   The links to save.
	 *
	 * @return void
	 */
	public function save_links( $post_id, array $links ) {
		foreach ( $links as $link ) {
			$this->table()->insert(
				[
					'url'            => $link->get_url(),
					'post_id'        => $post_id,
					'target_post_id' => $link->get_target_post_id(),
					'type'           => $link->get_type(),
				],
				[ '%s', '%d', '%d', '%s' ]
			);
		}
	}

	/**
	 * Update the link counts for a post and its referenced posts.
	 *
	 * @param int      $post_id Post to update.
	 * @param int|null $counts  Links count.
	 * @param Link[]   $links   Links to update incoming link count.
	 */
	public function update_link_counts( $post_id, $counts, array $links ) {
		$counts = wp_parse_args(
			$counts,
			[
				'internal_link_count' => 0,
				'external_link_count' => 0,
			]
		);

		$this->save_meta_data( $post_id, $counts );
		$this->update_incoming_links( $post_id, $links );
	}

	/**
	 * Update the incoming link count.
	 *
	 * @param int    $post_id Post which is processed.
	 * @param Link[] $links   Links we need to update the incoming link count of.
	 *
	 * @return void
	 */
	public function update_incoming_links( $post_id, $links ) {
		$post_ids = $this->get_internal_post_ids( $links );
		$post_ids = array_merge( [ $post_id ], $post_ids );
		$this->update_incoming_link_count( $post_ids );
	}

	/**
	 * Get post IDs from the link objects.
	 *
	 * @param Link[] $links Links we need to update the incoming link count of.
	 *
	 * @return int[] List of post IDs.
	 */
	protected function get_internal_post_ids( $links ) {
		$post_ids = [];
		foreach ( $links as $link ) {
			$post_ids[] = $link->get_target_post_id();
		}

		return array_filter( $post_ids );
	}

	/**
	 * Update the incoming link count.
	 *
	 * @param array $post_ids The posts to update the link count for.
	 */
	public function update_incoming_link_count( array $post_ids ) {
		$results = $this->table()
			->selectCount( 'id', 'incoming' )
			->select( 'target_post_id as post_id' )
			->whereIn( 'target_post_id', $post_ids )
			->groupBy( 'target_post_id' )->get();

		$post_ids_non_zero = [];
		foreach ( $results as $result ) {
			$this->save_meta_data( $result->post_id, [ 'incoming_link_count' => $result->incoming ] );
			$post_ids_non_zero[] = $result->post_id;
		}

		$post_ids_zero = array_diff( $post_ids, $post_ids_non_zero );
		foreach ( $post_ids_zero as $post_id ) {
			$this->save_meta_data( $post_id, [ 'incoming_link_count' => 0 ] );
		}
	}

	/**
	 * Save the link count to the database.
	 *
	 * @param int   $post_id   The ID to save the link count for.
	 * @param array $meta_data The total amount of links.
	 */
	public function save_meta_data( $post_id, array $meta_data ) {
		global $wpdb;

		// Suppress database errors and store current state.
		$last_suppressed_state = $wpdb->suppress_errors();

		$where  = [ 'object_id' => $post_id ];
		$data   = array_merge( $where, $meta_data );
		$result = $wpdb->insert( $wpdb->prefix . 'rank_math_internal_meta', $data );

		if ( false === $result ) {
			$result = $wpdb->update( $wpdb->prefix . 'rank_math_internal_meta', $data, $where );
		}

		// Revert to previous state of database error suppression.
		$wpdb->suppress_errors( $last_suppressed_state );

		return $result;
	}
}
