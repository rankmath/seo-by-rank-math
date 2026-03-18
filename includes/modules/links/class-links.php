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
use RankMath\Links\Admin\Admin;
use RankMath\Links\Api\Controller;

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
		$this->action( 'rest_api_init', 'register_rest_routes' );

		if ( is_admin() ) {
			new Admin();
		}
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes() {
		$controller = new Controller();
		$controller->register_routes();
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

		// Skip processing during meta-box-loader requests to avoid duplicate processing.
		// The actual save happens in the subsequent request.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['meta-box-loader'] ) ) {
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

		$processor = ContentProcessor::get();

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

		/**
		 * Filter to customize link count display in post list.
		 *
		 * Allows other plugins (like Rank Math PRO) to make link counts clickable.
		 *
		 * @param string $html    The HTML output for link counts.
		 * @param int    $post_id Post ID.
		 * @param object $counts  Link counts object with internal_link_count, external_link_count, incoming_link_count.
		 */
		$output = $this->do_filter(
			'links/post_column_display',
			$this->get_default_link_count_html( $post_id, $counts ),
			$post_id,
			$counts
		);

		echo wp_kses_post( $output );
	}

	/**
	 * Get default HTML for link counts display.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $counts  Link counts object.
	 *
	 * @return string HTML output.
	 */
	private function get_default_link_count_html( $post_id, $counts ) {
		ob_start();
		?>
		<span class="rank-math-column-display rank-math-link-count">
			<strong><?php esc_html_e( 'Links: ', 'rank-math' ); ?></strong>
			<?php
			/**
			 * Filter to customize individual link count item display.
			 *
			 * @param string $html      The HTML for this count item.
			 * @param string $type      Link type: 'internal', 'external', or 'incoming'.
			 * @param int    $count     The count value.
			 * @param int    $post_id   Post ID.
			 * @param object $counts    All link counts object.
			 */
			echo wp_kses_post( $this->get_link_count_item_html( 'internal', $counts->internal_link_count, $post_id ) );
			?>
			<span class="divider"></span>
			<?php
			echo wp_kses_post( $this->get_link_count_item_html( 'external', $counts->external_link_count, $post_id ) );
			?>
			<span class="divider"></span>
			<?php
			echo wp_kses_post( $this->get_link_count_item_html( 'incoming', $counts->incoming_link_count, $post_id ) );
			?>
		</span>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get HTML for a single link count item.
	 *
	 * Renders as a clickable link when count > 0, plain span otherwise.
	 *
	 * @param string $type    Link type: 'internal', 'external', or 'incoming'.
	 * @param int    $count   The count value.
	 * @param int    $post_id Post ID (used to build the Links page URL).
	 *
	 * @return string HTML output.
	 */
	private function get_link_count_item_html( $type, $count, $post_id ) {
		$icons = [
			'internal' => 'dashicons-admin-links',
			'external' => 'dashicons-external',
			'incoming' => 'dashicons-external internal',
		];

		$titles = [
			'internal' => __( 'Internal Links', 'rank-math' ),
			'external' => __( 'External Links', 'rank-math' ),
			'incoming' => __( 'Incoming Links', 'rank-math' ),
		];

		$inner = sprintf(
			'<span class="dashicons %1$s"></span><span>%2$s</span>',
			esc_attr( $icons[ $type ] ),
			esc_html( $count )
		);

		if ( empty( $count ) ) {
			return sprintf(
				'<span class="rank-math-link-count-item" data-link-type="%1$s" title="%2$s">%3$s</span>',
				esc_attr( $type ),
				esc_attr( $titles[ $type ] ),
				$inner
			);
		}

		$params = 'incoming' === $type
			? [
				'target_post_id' => absint( $post_id ),
				'link_type'      => 'internal',
			]
			: [
				'source_id' => absint( $post_id ),
				'link_type' => $type,
			];

		$url = Helper::get_admin_url( 'links-page' ) . '#links?' . http_build_query( $params );

		return sprintf(
			'<a href="%1$s" class="rank-math-link-count-item rank-math-link-count-clickable" data-link-type="%2$s" title="%3$s">%4$s</a>',
			esc_url( $url ),
			esc_attr( $type ),
			esc_attr( $titles[ $type ] ),
			$inner
		);
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
	 * Process a post (static method for use by PRO plugin).
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 */
	public static function process_post_links( $post_id, $post ) {
		if ( ! $post instanceof \WP_Post || ! self::is_post_processable( $post ) ) {
			return;
		}

		self::process( $post_id, $post->post_content );
	}

	/**
	 * Check if the post is processable (static method for use by PRO plugin).
	 *
	 * @param WP_Post $post The post object.
	 * @return bool True if processable.
	 */
	public static function is_post_processable( $post ) {
		/**
		 * Filter to prevent processing the post.
		 *
		 * @param boolean $value Whether to process the post.
		 * @param WP_POST $post  The Post object.
		 */
		if ( wp_is_post_revision( $post->ID ) || ! apply_filters( 'rank_math/links/process_post', true, $post ) ) {
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
	 * Process the content for a given post.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $content The content.
	 */
	private static function process( $post_id, $content ) {
		/**
		 * Filter to change the content passed to the Link processor.
		 *
		 * @param string $content Post content.
		 * @param int    $post_id Post ID.
		 */
		$content = apply_filters( 'rank_math/links/content', $content, $post_id );
		$content = str_replace( ']]>', ']]&gt;', $content );

		$processor = ContentProcessor::get();
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
