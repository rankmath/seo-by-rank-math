<?php
/**
 * The Link Counter module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Links
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Links;

use WP_Post;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Database\Database;
use RankMath\Admin\Post_Columns;

defined( 'ABSPATH' ) || exit;

/**
 * Links class.
 */
class Links {

	use Hooker;

	/**
	 * Links data.
	 *
	 * @var array
	 */
	private $links_data = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'save_post', 'save_post', 10, 2 );
		$this->action( 'delete_post', 'delete_post' );
		$this->action( 'rank_math/post/column/seo_details', 'post_column_content', 11, 3 );
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
		$processor->storage->update_link_counts( $post_id, [], $links );
	}

	/**
	 * Post column content.
	 *
	 * @param int   $post_id   Post ID.
	 * @param array $post_data Current post SEO data.
	 * @param array $data      All posts SEO data.
	 */
	public function post_column_content( $post_id, $post_data, $data ) {
		if ( ! Post_Columns::is_post_indexable( $post_id ) || empty( $data ) ) {
			return;
		}

		if ( empty( $this->links_data ) ) {
			$this->get_links_data( array_keys( $data ) );
		}

		$counts = ! empty( $this->links_data[ $post_id ] ) ? $this->links_data[ $post_id ] : (object) [
			'internal_link_count' => 0,
			'external_link_count' => 0,
			'incoming_link_count' => 0,
		];
		?>
		<span class="rank-math-column-display rank-math-link-count">
			<strong><?php esc_html_e( 'Links: ', 'rank-math' ); ?></strong>
			<span title="<?php esc_attr_e( 'Internal Links', 'rank-math' ); ?>">
				<span class="dashicons dashicons-admin-links"></span>
				<span><?php echo isset( $counts->internal_link_count ) ? esc_html( $counts->internal_link_count ) : ''; ?></span>
			</span>
			<span class="divider"></span>
			<span title="<?php esc_attr_e( 'External Links', 'rank-math' ); ?>">
				<span class="dashicons dashicons-external"></span>
				<span><?php echo isset( $counts->external_link_count ) ? esc_html( $counts->external_link_count ) : ''; ?></span>
			</span>
			<span class="divider"></span>
			<span title="<?php esc_attr_e( 'Incoming Links', 'rank-math' ); ?>">
				<span class="dashicons dashicons-external internal"></span>
				<span><?php echo isset( $counts->incoming_link_count ) ? esc_html( $counts->incoming_link_count ) : ''; ?></span>
			</span>
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
		/**
		 * Filter to change the content passed to the Link processor.
		 *
		 * @param string $content Post content.
		 * @param int    $post_id Post ID.
		 */
		$content = $this->do_filter( 'links/content', apply_filters( 'the_content', $content ), $post_id );
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
		/**
		 * Filter to prevent processing the post.
		 *
		 * @param boolean $value Whether to process the post.
		 * @param WP_POST $post  The Post object.
		 */
		if ( wp_is_post_revision( $post->ID ) || ! $this->do_filter( 'links/process_post', true, $post ) ) {
			return false;
		}

		if ( in_array( $post->post_status, [ 'auto-draft', 'trash' ], true ) ) {
			return false;
		}

		$post_types = Helper::get_accessible_post_types();
		unset( $post_types['attachment'] );

		return isset( $post_types[ $post->post_type ] );
	}

	/**
	 * Get links data by post id.
	 *
	 * @param array $post_ids The post ids.
	 */
	private function get_links_data( $post_ids ) {
		$results          = Database::table( 'rank_math_internal_meta' )->select( '*' )->whereIn( 'object_id', $post_ids )->groupBy( 'object_id' )->get();
		$this->links_data = array_combine( array_column( $results, 'object_id' ), $results );
	}
}
