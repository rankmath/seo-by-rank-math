<?php
/**
 * The Link Counter Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Links
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Links;

use WP_Post;
use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Links class.
 */
class Links {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			$this->action( 'save_post', 'save_post', 10, 2 );
			$this->action( 'delete_post', 'delete_post' );
			$this->action( 'rank_math_seo_details', 'post_column_content' );
		}

		$this->action( 'rank_math/links/internal_links', 'cron_job' );
	}

	/**
	 * Process and save the links in a post.
	 *
	 * @param int     $post_id The post ID to check.
	 * @param WP_Post $post    The post object.
	 */
	public function save_post( $post_id, $post ) {
		if ( ! $post instanceof WP_Post || ! $this->is_processable( $post ) ) {
			return;
		}

		$this->process( $post_id, $post->post_content );
	}

	/**
	 * Remove the links data when the post is deleted.
	 *
	 * @param int $post_id The post ID.
	 */
	public function delete_post( $post_id ) {
		if ( ! $this->is_processable( get_post( $post_id ) ) ) {
			return;
		}

		$processor = new ContentProcessor();

		// Get links to update linked objects.
		$links = $processor->get_stored_internal_links( $post_id );

		// Remove all links for this post.
		$processor->storage->cleanup( $post_id );

		// Update link counts.
		$processor->storage->update_link_counts( $post_id, 0, $links );
	}

	/**
	 * Post column content.
	 *
	 * @param int $post_id Post id.
	 */
	public function post_column_content( $post_id ) {
		if ( ! Helper::is_post_indexable( $post_id ) ) {
			return;
		}

		global $wpdb;

		$counts = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}rank_math_internal_meta WHERE object_id = {$post_id}" ); // phpcs:ignore
		$counts = ! empty( $counts ) ? $counts : (object) [
			'internal_link_count' => 0,
			'external_link_count' => 0,
			'incoming_link_count' => 0,
		];
		?>
		<span class="rank-math-column-display rank-math-link-count">
			<strong><?php esc_html_e( 'Links: ', 'rank-math' ); ?></strong>
			<span title="<?php esc_html_e( 'Internal Links', 'rank-math' ); ?>" class="dashicons dashicons-admin-links"></span> <span><?php echo isset( $counts->internal_link_count ) ? esc_html( $counts->internal_link_count ) : ''; ?></span>
			<span class="divider"></span>
			<span title="<?php esc_html_e( 'External Links', 'rank-math' ); ?>" class="dashicons dashicons-external"></span> <span><?php echo isset( $counts->external_link_count ) ? esc_html( $counts->external_link_count ) : ''; ?></span>
			<span class="divider"></span>
			<span title="<?php esc_html_e( 'Incoming Links', 'rank-math' ); ?>" class="dashicons dashicons-external internal"></span> <span><?php echo isset( $counts->incoming_link_count ) ? esc_html( $counts->incoming_link_count ) : ''; ?></span>
		</span>
		<?php
	}

	/**
	 * Process old posts if this is an old installation.
	 */
	public function cron_job() {
		$post_types = Helper::get_accessible_post_types();
		unset( $post_types['attachment'] );

		$posts = get_posts(
			[
				'post_type'   => array_keys( $post_types ),
				'post_status' => [ 'publish', 'future' ],
				'meta_query'  => [
					[
						'key'     => 'rank_math_internal_links_processed',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		// Early Bail.
		if ( empty( $posts ) ) {
			wp_clear_scheduled_hook( 'rank_math/links/internal_links' );
			return;
		}

		// Process.
		foreach ( $posts as $post ) {
			$this->save_post( $post->ID, $post );
		}
	}

	/**
	 * Process the content for a given post.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $content The content.
	 */
	private function process( $post_id, $content ) {
		// Apply the filters to get the real content.
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		$processor = new ContentProcessor();
		$processor->process( $post_id, $content );
		update_post_meta( $post_id, 'rank_math_internal_links_processed', true );
	}

	/**
	 * Check if the post is processable.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return bool True if processable.
	 */
	private function is_processable( $post ) {

		if ( wp_is_post_revision( $post->ID ) ) {
			return false;
		}

		if ( in_array( $post->post_status, [ 'auto-draft', 'trash' ], true ) ) {
			return false;
		}

		$post_types = Helper::get_accessible_post_types();
		unset( $post_types['attachment'] );

		return isset( $post_types[ $post->post_type ] );
	}
}
