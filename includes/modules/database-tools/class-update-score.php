<?php
/**
 * The tool to update SEO score on existing posts.
 *
 * @since      1.0.97
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;
use RankMath\Paper\Paper;
use RankMath\Admin\Metabox\Screen;
use RankMath\Helpers\DB as DB_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Update_Score class.
 */
class Update_Score {

	use Hooker;

	/**
	 * Batch size.
	 *
	 * @var int
	 */
	private $batch_size;

	/**
	 * Screen object.
	 *
	 * @var object
	 */
	public $screen;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->batch_size = absint( apply_filters( 'rank_math/recalculate_scores_batch_size', 25 ) );

		$this->filter( 'rank_math/tools/update_seo_score', 'update_seo_score' );

		$this->screen = new Screen();
		$this->screen->load_screen( 'post' );

		if ( Param::get( 'page' ) === 'rank-math-status' ) {
			$this->action( 'admin_enqueue_scripts', 'enqueue' );
		}
	}

	/**
	 * Enqueue scripts & add JSON data needed to update the SEO score on existing posts.
	 */
	public function enqueue() {
		$scripts = [
			'lodash'             => '',
			'wp-data'            => '',
			'wp-core-data'       => '',
			'wp-compose'         => '',
			'wp-components'      => '',
			'wp-element'         => '',
			'wp-block-editor'    => '',
			'wp-wordcount'       => '',
			'rank-math-analyzer' => rank_math()->plugin_url() . 'assets/admin/js/analyzer.js',
		];

		foreach ( $scripts as $handle => $src ) {
			wp_enqueue_script( $handle, $src, [], rank_math()->version, true );
		}

		global $post;
		$temp_post = $post;
		if ( is_null( $post ) ) {
			$posts = get_posts(
				[
					'fields'         => 'id',
					'posts_per_page' => 1,
					'post_type'      => $this->get_post_types(),
				]
			);
			$post  = isset( $posts[0] ) ? $posts[0] : null; //phpcs:ignore -- Overriding $post is required to load the localized data for the post.
		}

		$this->screen->localize();
		$post = $temp_post; //phpcs:ignore -- Overriding $post is required to load the localized data for the post.

		Helper::add_json( 'totalPostsWithoutScore', $this->find( false ) );
		Helper::add_json( 'totalPosts', $this->find( true ) );
		Helper::add_json( 'batchSize', $this->batch_size );
	}

	/**
	 * Function to Update the SEO score.
	 */
	public function update_seo_score() {
		$args   = Param::post( 'args', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$offset = isset( $args['offset'] ) ? absint( $args['offset'] ) : 0;

		// We get "paged" when running from the importer.
		$paged = Param::post( 'paged', 0 );
		if ( $paged ) {
			$offset = ( $paged - 1 ) * $this->batch_size;
		}

		$update_all = ! isset( $args['update_all_scores'] ) || ! empty( $args['update_all_scores'] );
		$query_args = [
			'post_type'      => $this->get_post_types(),
			'posts_per_page' => $this->batch_size,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'status'         => 'any',
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => 'rank_math_focus_keyword',
					'value'   => '',
					'compare' => '!=',
				],
			],
		];

		if ( ! $update_all ) {
			$query_args['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'     => 'rank_math_seo_score',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => 'rank_math_seo_score',
					'value'   => '',
					'compare' => '=',
				],
			];
		}

		$posts = get_posts( $query_args );

		if ( empty( $posts ) ) {
			return 'complete'; // Don't translate this string.
		}

		add_filter(
			'rank_math/replacements/non_cacheable',
			function ( $non_cacheable ) {
				$non_cacheable[] = 'excerpt';
				$non_cacheable[] = 'excerpt_only';
				$non_cacheable[] = 'seo_description';
				$non_cacheable[] = 'keywords';
				$non_cacheable[] = 'focuskw';
				return $non_cacheable;
			}
		);

		rank_math()->variables->setup();
		$data = [];
		foreach ( $posts as $post ) {
			$post_id   = $post->ID;
			$post_type = $post->post_type;
			$title     = get_post_meta( $post_id, 'rank_math_title', true );
			$title     = $title ? $title : Paper::get_from_options( "pt_{$post_type}_title", $post, '%title% %sep% %sitename%' );
			$keywords  = array_map( 'trim', explode( ',', Helper::get_post_meta( 'focus_keyword', $post_id ) ) );
			$keyword   = $keywords[0];

			$values = [
				'title'        => Helper::replace_vars( '%seo_title%', $post ),
				'description'  => Helper::replace_vars( '%seo_description%', $post ),
				'keywords'     => $keywords,
				'keyword'      => $keyword,
				'content'      => wpautop( $post->post_content ),
				'url'          => urldecode( get_the_permalink( $post_id ) ),
				'hasContentAi' => ! empty( Helper::get_post_meta( 'contentai_score', $post_id ) ),
			];

			if ( has_post_thumbnail( $post_id ) ) {
				$thumbnail_id           = get_post_thumbnail_id( $post_id );
				$values['thumbnail']    = get_the_post_thumbnail_url( $post_id );
				$values['thumbnailAlt'] = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
			}

			/**
			 * Filter the values sent to the analyzer to calculate the SEO score.
			 *
			 * @param array $values The values to be sent to the analyzer.
			 */
			$data[ $post_id ] = $this->do_filter( 'recalculate_score/data', $values, $post_id );
		}

		return $data;
	}

	/**
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Update_Score
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Update_Score ) ) {
			$instance = new Update_Score();
		}

		return $instance;
	}

	/**
	 * Find posts with focus keyword but no SEO score.
	 *
	 * @param  bool $update_all Whether to update all posts or only those without a score.
	 * @return int
	 */
	public function find( $update_all = true ) {
		global $wpdb;
		$post_types  = $this->get_post_types();
		$placeholder = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
		$query       = "SELECT COUNT(ID) FROM {$wpdb->posts} as p
			LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_focus_keyword'
			WHERE p.post_type IN ({$placeholder}) AND p.post_status = 'publish' AND pm.meta_value != ''";

		if ( ! $update_all ) {
			$query .= " AND (SELECT COUNT(*) FROM {$wpdb->postmeta} as pm2 WHERE pm2.post_id = p.ID AND pm2.meta_key = 'rank_math_seo_score' AND pm2.meta_value != '') = 0";
		}

		$update_score_post_ids = DB_Helper::get_var( $wpdb->prepare( $query, $post_types ) );

		return (int) $update_score_post_ids;
	}

	/**
	 * Get post types.
	 *
	 * @return array
	 */
	private function get_post_types() {
		$post_types = get_post_types( [ 'public' => true ] );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		return $this->do_filter( 'tool/post_types', array_keys( $post_types ) );
	}
}
