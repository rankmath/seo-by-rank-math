<?php
/**
 * Links REST API controller.
 *
 * Handles links-related endpoints for the Free plugin admin page:
 * /posts, /posts-stats, /links, /links-stats.
 *
 * @since      1.0.266
 * @package    RankMath
 * @subpackage RankMath\Links\Api
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Links\Api;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Controller class.
 *
 * Provides REST endpoints for basic links and posts data using
 * only Free plugin tables (rank_math_internal_meta, rank_math_internal_links).
 * PRO plugin can override responses via filters to supply richer data.
 */
class Controller {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'rankmath/v1';

	/**
	 * Cache group for object caching.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'rank_math_links';

	/**
	 * Cache TTL in seconds (30 minutes).
	 *
	 * @var int
	 */
	const CACHE_TTL = 1800;

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		// GET /rankmath/v1/links/posts.
		register_rest_route(
			self::NAMESPACE,
			'/links/posts',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_posts' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => $this->get_posts_args(),
			]
		);

		// GET /rankmath/v1/links/posts-stats.
		register_rest_route(
			self::NAMESPACE,
			'/links/posts-stats',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_posts_stats' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		// GET /rankmath/v1/links/links.
		register_rest_route(
			self::NAMESPACE,
			'/links/links',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_links' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => $this->get_links_args(),
			]
		);

		// GET /rankmath/v1/links/links-stats.
		register_rest_route(
			self::NAMESPACE,
			'/links/links-stats',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_links_stats' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);
	}

	/**
	 * Permission callback: require manage_options capability.
	 *
	 * @return bool|\WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You do not have permission to access this resource.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}
		return true;
	}

	/**
	 * Get posts with link metrics.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_posts( $request ) {
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		$args = [
			'search'          => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'post_type'       => (array) $request->get_param( 'post_type' ),
			'is_orphan'       => sanitize_text_field( (string) $request->get_param( 'is_orphan' ) ),
			'seo_score_range' => sanitize_text_field( (string) $request->get_param( 'seo_score_range' ) ),
			'orderby'         => sanitize_text_field( (string) $request->get_param( 'orderby' ) ),
			'order'           => sanitize_text_field( (string) $request->get_param( 'order' ) ),
			'offset'          => $offset,
			'per_page'        => $per_page,
		];

		/**
		 * Filter to allow overriding the posts REST response.
		 *
		 * Return a non-null array to bypass the Free plugin's query entirely.
		 * Expected format: [ 'posts' => array, 'total' => int, 'pages' => int ]
		 *
		 * @since 1.0.266
		 *
		 * @param null|array $override Return override data or null to use Free's query.
		 * @param array      $args     Normalized query arguments.
		 */
		$override = apply_filters( 'rank_math/links/rest_posts_response', null, $args );
		if ( null !== $override ) {
			return rest_ensure_response( $override );
		}

		$results = $this->query_posts( $args );
		$total   = $this->query_posts_count( $args );

		return rest_ensure_response(
			[
				'posts' => $results,
				'total' => (int) $total,
				'pages' => (int) ceil( $total / $per_page ),
			]
		);
	}

	/**
	 * Get aggregate posts statistics.
	 *
	 * @return WP_REST_Response
	 */
	public function get_posts_stats() {
		/**
		 * Filter to allow overriding the posts-stats REST response.
		 *
		 * @since 1.0.266
		 *
		 * @param null|array $override Return override data or null to use Free's query.
		 */
		$override = apply_filters( 'rank_math/links/rest_posts_stats_response', null );
		if ( null !== $override ) {
			return rest_ensure_response( $override );
		}

		$cache_key = 'posts_stats';
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		global $wpdb;

		$stats = $wpdb->get_row(
			"SELECT
				COUNT(DISTINCT m.object_id) as total_posts,
				SUM(CASE WHEN m.incoming_link_count IS NULL OR m.incoming_link_count = 0 THEN 1 ELSE 0 END) as orphan_posts,
				SUM(CASE WHEN m.internal_link_count > 0 THEN 1 ELSE 0 END) as posts_with_internal,
				SUM(CASE WHEN m.external_link_count > 0 THEN 1 ELSE 0 END) as posts_with_external
			FROM {$wpdb->prefix}rank_math_internal_meta m
			INNER JOIN {$wpdb->posts} p ON m.object_id = p.ID
			WHERE p.post_status = 'publish'"
		);

		$response = [
			'total_posts'         => (int) ( $stats->total_posts ?? 0 ),
			'orphan_posts'        => (int) ( $stats->orphan_posts ?? 0 ),
			'posts_with_internal' => (int) ( $stats->posts_with_internal ?? 0 ),
			'posts_with_external' => (int) ( $stats->posts_with_external ?? 0 ),
		];

		wp_cache_set( $cache_key, $response, self::CACHE_GROUP, self::CACHE_TTL );

		return rest_ensure_response( $response );
	}

	/**
	 * Get links with basic columns.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_links( $request ) {
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		$args = [
			'search'         => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'source_id'      => absint( $request->get_param( 'source_id' ) ),
			'target_post_id' => absint( $request->get_param( 'target_post_id' ) ),
			'is_internal'    => sanitize_text_field( (string) $request->get_param( 'is_internal' ) ),
			'orderby'        => sanitize_text_field( (string) $request->get_param( 'orderby' ) ),
			'order'          => sanitize_text_field( (string) $request->get_param( 'order' ) ),
			'offset'         => $offset,
			'per_page'       => $per_page,
		];

		/**
		 * Filter to allow overriding the links REST response.
		 *
		 * Return a non-null array to bypass the Free plugin's query entirely.
		 * Expected format: [ 'links' => array, 'total' => int, 'pages' => int ]
		 *
		 * @since 1.0.266
		 *
		 * @param null|array $override Return override data or null to use Free's query.
		 * @param array      $args     Normalized query arguments.
		 */
		$override = apply_filters( 'rank_math/links/rest_links_response', null, $args );
		if ( null !== $override ) {
			return rest_ensure_response( $override );
		}

		$results = $this->query_links( $args );
		$total   = $this->query_links_count( $args );

		return rest_ensure_response(
			[
				'links' => $results,
				'total' => (int) $total,
				'pages' => (int) ceil( $total / $per_page ),
			]
		);
	}

	/**
	 * Get basic links statistics (total, internal, external).
	 *
	 * @return WP_REST_Response
	 */
	public function get_links_stats() {
		/**
		 * Filter to allow overriding the links-stats REST response.
		 *
		 * @since 1.0.266
		 *
		 * @param null|array $override Return override data or null to use Free's query.
		 */
		$override = apply_filters( 'rank_math/links/rest_links_stats_response', null );
		if ( null !== $override ) {
			return rest_ensure_response( $override );
		}

		$cache_key = 'links_stats';
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'rank_math_internal_links';

		$stats = $wpdb->get_row(
			"SELECT
				COUNT(*) as total,
				SUM(CASE WHEN type = 'internal' THEN 1 ELSE 0 END) as internal,
				SUM(CASE WHEN type = 'external' THEN 1 ELSE 0 END) as external
			FROM {$table}"
		);

		$response = [
			'total'    => (int) ( $stats->total ?? 0 ),
			'internal' => (int) ( $stats->internal ?? 0 ),
			'external' => (int) ( $stats->external ?? 0 ),
		];

		wp_cache_set( $cache_key, $response, self::CACHE_GROUP, self::CACHE_TTL );

		return rest_ensure_response( $response );
	}

	/**
	 * Query posts with link metrics from Free tables.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	private function query_posts( $args ) {
		$cache_key = 'posts_' . md5( wp_json_encode( $args ) );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$where_sql = $this->build_posts_where( $args );
		$orderby   = $this->validate_posts_orderby( $args['orderby'] );
		$order     = $this->validate_order( $args['order'], 'ASC' );
		$limit_sql = $wpdb->prepare( 'LIMIT %d OFFSET %d', $args['per_page'], $args['offset'] );

		$results = $wpdb->get_results(
			"SELECT p.ID as post_id,
					p.post_title,
					p.post_type,
					p.post_modified,
					m.internal_link_count,
					m.external_link_count,
					m.incoming_link_count,
					pm_score.meta_value as seo_score
			FROM {$wpdb->prefix}rank_math_internal_meta m
			INNER JOIN {$wpdb->posts} p ON m.object_id = p.ID
			LEFT JOIN {$wpdb->postmeta} pm_score ON p.ID = pm_score.post_id AND pm_score.meta_key = 'rank_math_seo_score'
			WHERE {$where_sql}
			ORDER BY {$orderby} {$order}
			{$limit_sql}"
		);

		foreach ( $results as $row ) {
			$row->internal_link_count = (int) ( $row->internal_link_count ?? 0 );
			$row->external_link_count = (int) ( $row->external_link_count ?? 0 );
			$row->incoming_link_count = (int) ( $row->incoming_link_count ?? 0 );
			$row->seo_score           = $row->seo_score ? (int) $row->seo_score : 0;
			$row->is_orphan           = 0 === $row->incoming_link_count;
			$row->edit_url            = get_edit_post_link( $row->post_id, '&' );
			$row->post_url            = $this->get_relative_permalink( $row->post_id );
			$post_type_obj            = get_post_type_object( $row->post_type );
			$row->post_type_label     = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst( $row->post_type );
		}

		wp_cache_set( $cache_key, $results, self::CACHE_GROUP, self::CACHE_TTL );

		return $results;
	}

	/**
	 * Count posts matching query arguments.
	 *
	 * @param array $args Query arguments.
	 * @return int
	 */
	private function query_posts_count( $args ) {
		$cache_key = 'posts_count_' . md5( wp_json_encode( $args ) );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$where_sql = $this->build_posts_where( $args );
		$joins     = "INNER JOIN {$wpdb->posts} p ON m.object_id = p.ID";

		if ( ! empty( $args['seo_score_range'] ) ) {
			$joins .= " LEFT JOIN {$wpdb->postmeta} pm_score ON p.ID = pm_score.post_id AND pm_score.meta_key = 'rank_math_seo_score'";
		}

		$count = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT m.object_id)
			FROM {$wpdb->prefix}rank_math_internal_meta m
			{$joins}
			WHERE {$where_sql}"
		);

		wp_cache_set( $cache_key, $count, self::CACHE_GROUP, self::CACHE_TTL );

		return $count;
	}

	/**
	 * Query links from Free tables.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	private function query_links( $args ) {
		$cache_key = 'links_' . md5( wp_json_encode( $args ) );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$where_sql = $this->build_links_where( $args );
		$orderby   = $this->validate_links_orderby( $args['orderby'] );
		$order     = $this->validate_order( $args['order'], 'DESC' );
		$limit_sql = $wpdb->prepare( 'LIMIT %d OFFSET %d', $args['per_page'], $args['offset'] );
		$table     = $wpdb->prefix . 'rank_math_internal_links';

		$results = $wpdb->get_results(
			"SELECT l.id, l.url, l.post_id, l.target_post_id, l.type,
					sp.post_title as source_title,
					tp.post_title as target_title
			FROM {$table} l
			LEFT JOIN {$wpdb->posts} sp ON l.post_id = sp.ID
			LEFT JOIN {$wpdb->posts} tp ON l.target_post_id = tp.ID
			WHERE {$where_sql}
			ORDER BY {$orderby} {$order}
			{$limit_sql}"
		);

		foreach ( $results as $row ) {
			$row->source_url      = $this->get_relative_permalink( $row->post_id );
			$row->source_edit_url = $row->post_id ? get_edit_post_link( $row->post_id, '&' ) : '';
			$row->target_url      = ( 'internal' === $row->type ) ? $this->get_relative_permalink( $row->target_post_id ) : '';
		}

		wp_cache_set( $cache_key, $results, self::CACHE_GROUP, self::CACHE_TTL );

		return $results;
	}

	/**
	 * Count links matching query arguments.
	 *
	 * @param array $args Query arguments.
	 * @return int
	 */
	private function query_links_count( $args ) {
		$cache_key = 'links_count_' . md5( wp_json_encode( $args ) );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$table     = $wpdb->prefix . 'rank_math_internal_links';
		$where_sql = $this->build_links_where( $args );

		$count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} l WHERE {$where_sql}"
		);

		wp_cache_set( $cache_key, $count, self::CACHE_GROUP, self::CACHE_TTL );

		return $count;
	}

	/**
	 * Build WHERE clause for posts query.
	 *
	 * @param array $args Query arguments.
	 * @return string
	 */
	private function build_posts_where( $args ) {
		global $wpdb;

		$where = [ "p.post_status = 'publish'" ];

		// Post type filter.
		$post_types = array_filter( array_map( 'sanitize_text_field', (array) $args['post_type'] ) );
		if ( ! empty( $post_types ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$where[] = $wpdb->prepare( 'p.post_type IN (' . $placeholders . ')', ...$post_types );
		} else {
			$where[] = "p.post_type IN ('post', 'page')";
		}

		// Orphan filter.
		if ( isset( $args['is_orphan'] ) && '' !== $args['is_orphan'] ) {
			if ( 'orphan' === $args['is_orphan'] ) {
				$where[] = '(m.incoming_link_count IS NULL OR m.incoming_link_count = 0)';
			} elseif ( 'linked' === $args['is_orphan'] ) {
				$where[] = 'm.incoming_link_count > 0';
			}
		}

		// SEO score range filter.
		if ( ! empty( $args['seo_score_range'] ) ) {
			switch ( $args['seo_score_range'] ) {
				case 'great':
					$where[] = 'CAST(pm_score.meta_value AS UNSIGNED) > 80';
					break;
				case 'good':
					$where[] = 'CAST(pm_score.meta_value AS UNSIGNED) BETWEEN 51 AND 80';
					break;
				case 'bad':
					$where[] = 'CAST(pm_score.meta_value AS UNSIGNED) <= 50';
					break;
				case 'no-score':
					$where[] = "(pm_score.meta_value IS NULL OR pm_score.meta_value = '')";
					break;
			}
		}

		// Search filter.
		if ( ! empty( $args['search'] ) ) {
			$where[] = $wpdb->prepare(
				'p.post_title LIKE %s',
				'%' . $wpdb->esc_like( $args['search'] ) . '%'
			);
		}

		return implode( ' AND ', $where );
	}

	/**
	 * Build WHERE clause for links query.
	 *
	 * @param array $args Query arguments.
	 * @return string
	 */
	private function build_links_where( $args ) {
		global $wpdb;

		$where = [ '1=1' ];

		if ( ! empty( $args['source_id'] ) ) {
			$where[] = $wpdb->prepare( 'l.post_id = %d', $args['source_id'] );
		}

		if ( ! empty( $args['target_post_id'] ) ) {
			$where[] = $wpdb->prepare( 'l.target_post_id = %d', $args['target_post_id'] );
		}

		if ( isset( $args['is_internal'] ) && '' !== $args['is_internal'] ) {
			$type    = '1' === $args['is_internal'] ? 'internal' : 'external';
			$where[] = $wpdb->prepare( 'l.type = %s', $type );
		}

		if ( ! empty( $args['search'] ) ) {
			$where[] = $wpdb->prepare(
				'l.url LIKE %s',
				'%' . $wpdb->esc_like( $args['search'] ) . '%'
			);
		}

		return implode( ' AND ', $where );
	}

	/**
	 * Validate ORDER direction.
	 *
	 * @param string $order         Input order.
	 * @param string $default_order Default order.
	 * @return string
	 */
	private function validate_order( $order, $default_order = 'DESC' ) {
		$order = strtoupper( $order );
		return in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : $default_order;
	}

	/**
	 * Validate and return ORDER BY column for posts query.
	 *
	 * @param string $orderby Input orderby.
	 * @return string
	 */
	private function validate_posts_orderby( $orderby ) {
		$map = [
			'post_title'          => 'p.post_title',
			'internal_link_count' => 'm.internal_link_count',
			'external_link_count' => 'm.external_link_count',
			'incoming_link_count' => 'm.incoming_link_count',
			'seo_score'           => 'pm_score.meta_value',
			'post_modified'       => 'p.post_modified',
		];
		return isset( $map[ $orderby ] ) ? $map[ $orderby ] : 'p.post_title';
	}

	/**
	 * Validate and return ORDER BY column for links query.
	 *
	 * @param string $orderby Input orderby.
	 * @return string
	 */
	private function validate_links_orderby( $orderby ) {
		$map = [
			'id'           => 'l.id',
			'url'          => 'l.url',
			'type'         => 'l.type',
			'source_title' => 'sp.post_title',
		];
		return isset( $map[ $orderby ] ) ? $map[ $orderby ] : 'l.id';
	}

	/**
	 * Get relative permalink (without home_url prefix).
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_relative_permalink( $post_id ) {
		if ( empty( $post_id ) ) {
			return '';
		}
		$permalink = get_permalink( $post_id );
		if ( ! $permalink ) {
			return '';
		}
		return str_replace( untrailingslashit( home_url() ), '', $permalink );
	}

	/**
	 * Get argument definitions for the /posts endpoint.
	 *
	 * @return array
	 */
	private function get_posts_args() {
		return [
			'page'            => [
				'description' => esc_html__( 'Page number.', 'rank-math' ),
				'type'        => 'integer',
				'default'     => 1,
			],
			'per_page'        => [
				'description' => esc_html__( 'Items per page.', 'rank-math' ),
				'type'        => 'integer',
				'default'     => 50,
				'maximum'     => 100,
			],
			'search'          => [
				'description'       => esc_html__( 'Search in post title.', 'rank-math' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'post_type'       => [
				'description'       => esc_html__( 'Filter by post type. Supports multiple values.', 'rank-math' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'string' ],
				'default'           => [],
				'sanitize_callback' => function ( $param ) {
					return array_map( 'sanitize_text_field', (array) $param );
				},
			],
			'is_orphan'       => [
				'description'       => esc_html__( 'Filter by orphan status (orphan, linked).', 'rank-math' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'seo_score_range' => [
				'description'       => esc_html__( 'Filter by SEO score range (great, good, bad, no-score).', 'rank-math' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'orderby'         => [
				'description'       => esc_html__( 'Order by field.', 'rank-math' ),
				'type'              => 'string',
				'default'           => 'post_title',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'order'           => [
				'description'       => esc_html__( 'Order direction (ASC, DESC).', 'rank-math' ),
				'type'              => 'string',
				'default'           => 'ASC',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get argument definitions for the /links endpoint.
	 *
	 * @return array
	 */
	private function get_links_args() {
		return [
			'page'           => [
				'description' => esc_html__( 'Page number.', 'rank-math' ),
				'type'        => 'integer',
				'default'     => 1,
			],
			'per_page'       => [
				'description' => esc_html__( 'Items per page.', 'rank-math' ),
				'type'        => 'integer',
				'default'     => 50,
				'maximum'     => 100,
			],
			'search'         => [
				'description'       => esc_html__( 'Search in URL.', 'rank-math' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'source_id'      => [
				'description' => esc_html__( 'Filter by source post ID.', 'rank-math' ),
				'type'        => 'integer',
				'default'     => 0,
			],
			'target_post_id' => [
				'description' => esc_html__( 'Filter by target post ID.', 'rank-math' ),
				'type'        => 'integer',
				'default'     => 0,
			],
			'is_internal'    => [
				'description'       => esc_html__( 'Filter by link type (1 = internal, 0 = external).', 'rank-math' ),
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'orderby'        => [
				'description'       => esc_html__( 'Order by field.', 'rank-math' ),
				'type'              => 'string',
				'default'           => 'id',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'order'          => [
				'description'       => esc_html__( 'Order direction (ASC, DESC).', 'rank-math' ),
				'type'              => 'string',
				'default'           => 'DESC',
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}
