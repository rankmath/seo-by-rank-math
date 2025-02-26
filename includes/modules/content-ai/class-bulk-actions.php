<?php
/**
 * Add Content AI Bulk Action options.
 *
 * @since      1.0.212
 * @package    RankMath
 * @subpackage RankMath\Content_AI_Page
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Paper\Paper;
use RankMath\Admin\Admin_Helper;
use RankMath\Post;

defined( 'ABSPATH' ) || exit;

/**
 * Bulk_Actions class.
 */
class Bulk_Actions {
	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'init' );
		$this->action( 'admin_init', 'init_admin', 15 );
		$this->action( 'rank_math/content_ai/generate_alt', 'generate_image_alt' );
		$this->filter( 'rank_math/database/tools', 'add_tools' );
		$this->filter( 'rank_math/tools/content_ai_cancel_bulk_edit_process', 'cancel_bulk_edit_process' );
	}

	/**
	 * Init function.
	 */
	public function init() {
		Bulk_Edit_SEO_Meta::get();
		Bulk_Image_Alt::get();
	}

	/**
	 * Init.
	 */
	public function init_admin() {
		// Add Bulk actions for Posts.
		$post_types = Helper::get_settings( 'general.content_ai_post_types', [] );
		foreach ( $post_types as $post_type ) {
			$this->filter( "bulk_actions-edit-{$post_type}", 'bulk_actions', 9 );
			$this->filter( "handle_bulk_actions-edit-{$post_type}", 'handle_bulk_actions', 10, 3 );
		}

		// Add Bulk Generate on Attachment page.
		$this->filter( 'bulk_actions-upload', 'bulk_actions_attachment' );
		$this->filter( 'handle_bulk_actions-upload', 'handle_bulk_actions', 10, 3 );

		// Add Bulk Actions for Taxonomies.
		$taxonomies = Helper::get_accessible_taxonomies();
		unset( $taxonomies['post_format'] );
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		foreach ( $taxonomies as $taxonomy => $label ) {
			$this->filter( "bulk_actions-edit-{$taxonomy}", 'bulk_actions' );
			$this->filter( "handle_bulk_actions-edit-{$taxonomy}", 'handle_bulk_actions', 10, 3 );
		}

		$this->filter( 'wp_bulk_edit_seo_meta_post_args', 'update_background_process_args' );
		$this->filter( 'wp_bulk_image_alt_post_args', 'update_background_process_args' );
	}

	/**
	 * Add bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  array $actions Actions.
	 * @return array          New actions.
	 */
	public function bulk_actions( $actions ) {
		if ( ! Helper::has_cap( 'content_ai' ) ) {
			return $actions;
		}

		$actions['rank_math_ai_options']                             = __( '&#8595; Rank Math Content AI', 'rank-math' );
		$actions['rank_math_content_ai_fetch_seo_title']             = esc_html__( 'Write SEO Title with AI', 'rank-math' );
		$actions['rank_math_content_ai_fetch_seo_description']       = esc_html__( 'Write SEO Description with AI', 'rank-math' );
		$actions['rank_math_content_ai_fetch_seo_title_description'] = esc_html__( 'Write SEO Title & Description with AI', 'rank-math' );
		$actions['rank_math_content_ai_fetch_image_alt']             = esc_html__( 'Write Image Alt Text with AI', 'rank-math' );

		return $actions;
	}

	/**
	 * Add bulk actions for Attachment.
	 *
	 * @param  array $actions Actions.
	 * @return array          New actions.
	 */
	public function bulk_actions_attachment( $actions ) {
		if ( ! Helper::has_cap( 'content_ai' ) ) {
			return $actions;
		}

		$actions['rank_math_ai_options']                 = __( '&#8595; Rank Math Content AI', 'rank-math' );
		$actions['rank_math_content_ai_fetch_image_alt'] = esc_html__( 'Write Image Alt Text with AI', 'rank-math' );

		return $actions;
	}

	/**
	 * Handle bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  string $redirect   Redirect URL.
	 * @param  string $doaction   Performed action.
	 * @param  array  $object_ids Post IDs.
	 *
	 * @return string New redirect URL.
	 */
	public function handle_bulk_actions( $redirect, $doaction, $object_ids ) {
		if ( empty( $object_ids ) || ! in_array( $doaction, [ 'rank_math_content_ai_fetch_seo_title', 'rank_math_content_ai_fetch_seo_description', 'rank_math_content_ai_fetch_seo_title_description', 'rank_math_content_ai_fetch_image_alt' ], true ) ) {
			return $redirect;
		}

		if ( ! empty( get_option( 'rank_math_content_ai_posts' ) ) ) {
			Helper::add_notification(
				esc_html__( 'Another bulk editing process is already running. Please try again later after the existing process is complete.', 'rank-math' ),
				[
					'type'    => 'warning',
					'id'      => 'rank_math_content_ai_posts_error',
					'classes' => 'rank-math-notice',
				]
			);

			return $redirect;
		}

		if ( 'rank_math_content_ai_fetch_image_alt' === $doaction ) {
			$this->generate_image_alt( $object_ids );
			return $redirect;
		}

		$action = 'both';
		if ( 'rank_math_content_ai_fetch_seo_title' === $doaction ) {
			$action = 'title';
		}

		if ( 'rank_math_content_ai_fetch_seo_description' === $doaction ) {
			$action = 'description';
		}

		$is_post_list = Admin_Helper::is_post_list();
		$data         = [
			'action'      => $action,
			'language'    => Helper::get_settings( 'general.content_ai_language', Helper::content_ai_default_language() ),
			'posts'       => [],
			'is_taxonomy' => ! $is_post_list,
		];

		$method = $is_post_list ? 'get_post_data' : 'get_term_data';
		foreach ( $object_ids as $object_id ) {
			$data['posts'][] = $this->$method( $object_id );
		}

		Bulk_Edit_SEO_Meta::get()->start( $data );

		return $redirect;
	}

	/**
	 * Generate Image Alt for the attachmed Ids.
	 *
	 * @param array $object_ids Attachment Ids.
	 */
	public function generate_image_alt( $object_ids ) {
		$data = [
			'action' => 'image_alt',
			'posts'  => [],
		];

		foreach ( $object_ids as $object_id ) {
			if ( get_post_type( $object_id ) === 'attachment' ) {
				$data['posts'][ $object_id ] = [ wp_get_attachment_url( $object_id ) ];
				continue;
			}
			// Get all <img> tags from the post content.
			$images = [];
			preg_match_all( '/<img\\s[^>]+>/i', get_post_field( 'post_content', $object_id ), $images );

			// Keep only the image tags that have src attribute but no alt attribute.
			$images = array_filter(
				$images[0],
				function ( $image ) {
					return preg_match( '/src=[\'"]?([^\'" >]+)[\'" >]/i', $image, $matches ) && ( ! preg_match( '/alt="([^"]*)"/i', $image, $matches ) || preg_match( '/alt=""/i', $image, $matches ) );
				}
			);

			if ( empty( $images ) ) {
				continue;
			}

			$object                      = get_post( $object_id );
			$data['posts'][ $object_id ] = array_filter( array_values( $images ) );
		}

		Bulk_Image_Alt::get()->start( $data );
	}

	/**
	 * Change the timeout value in Background_Process to resolve the issue with notifications not appearing after completion in v1.2.
	 *
	 * @param array $args Process args.
	 *
	 * @return array
	 */
	public function update_background_process_args( $args ) {
		$args['timeout'] = 0.01;

		return $args;
	}

	/**
	 * Add database tools.
	 *
	 * @param array $tools Array of tools.
	 *
	 * @return array
	 */
	public function add_tools( $tools ) {
		$posts = get_option( 'rank_math_content_ai_posts' );

		// Early Bail if process is not running.
		if ( empty( $posts ) ) {
			return $tools;
		}

		$processed = get_option( 'rank_math_content_ai_posts_processed' );

		$tools['content_ai_cancel_bulk_edit_process'] = [
			'title'       => esc_html__( 'Cancel Content AI Bulk Editing Process', 'rank-math' ),
			'description' => sprintf(
				// Translators: placeholders are the number of posts that were processed.
				esc_html__( 'Terminate the ongoing Content AI Bulk Editing Process to halt any pending modifications and revert to the previous state. The bulk metadata has been generated for %1$d out of %1$d posts so far.', 'rank-math' ),
				$processed,
				count( $posts )
			),
			'button_text' => esc_html__( 'Terminate', 'rank-math' ),
		];

		return $tools;
	}

	/**
	 * Function to cancel the Bulk Edit process.
	 */
	public function cancel_bulk_edit_process() {
		Bulk_Edit_SEO_Meta::get()->cancel();
		Helper::remove_notification( 'rank_math_content_ai_posts_started' );
		return __( 'Bulk Editing Process Successfully Cancelled', 'rank-math' );
	}

	/**
	 * Get Post data.
	 *
	 * @param integer $object_id Post ID.
	 *
	 * @return array Post data.
	 */
	private function get_post_data( $object_id ) {
		$object = get_post( $object_id );
		return [
			'post_id'       => $object_id,
			'post_type'     => 'download' === $object->post_type ? 'Product' : ucfirst( $object->post_type ),
			'title'         => get_the_title( $object_id ),
			'focus_keyword' => Post::get_meta( 'focus_keyword', $object_id ),
			'summary'       => Helper::replace_vars( $this->get_post_description( $object ), $object ),
		];
	}

	/**
	 * Get Term data.
	 *
	 * @param integer $object_id Term ID.
	 *
	 * @return array Term data.
	 */
	private function get_term_data( $object_id ) {
		$object = get_term( $object_id );
		return [
			'post_id'       => $object_id,
			'post_type'     => $object->taxonomy,
			'title'         => $object->name,
			'focus_keyword' => get_term_meta( $object_id, 'rank_math_focus_keyword', true ),
			'summary'       => Helper::replace_vars( $this->get_term_description( $object ), $object ),
		];
	}

	/**
	 * Get post description.
	 *
	 * @param WP_Post $post Post Instance.
	 *
	 * @return string Post description.
	 */
	private function get_post_description( $post ) {
		$description = Post::get_meta( 'description', $post->ID );
		if ( '' !== $description ) {
			return $description;
		}

		return ! empty( $post->post_excerpt ) ? $post->post_excerpt : Str::truncate( Paper::get_from_options( "pt_{$post->post_type}_description", $post ), 160 );
	}

	/**
	 * Get post description.
	 *
	 * @param WP_Term $term Post Instance.
	 *
	 * @return string Post description.
	 */
	private function get_term_description( $term ) {
		$description = get_term_meta( $term->term_id, 'rank_math_description', true );
		if ( '' !== $description ) {
			return $description;
		}

		return ! empty( $term->description ) ? $term->description : Str::truncate( Paper::get_from_options( "tax_{$term->taxonomy}_description", $term ), 160 );
	}
}
