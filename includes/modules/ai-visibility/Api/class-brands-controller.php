<?php
/**
 * Brands REST API controller.
 *
 * Cache-backed proxy for the AI Visibility backend with SWR caching.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility\Api
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility\Api;

use WP_REST_Server;
use WP_REST_Request;
use RankMath\AI_Visibility\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Brands_Controller class.
 */
class Brands_Controller extends Base_Controller {

	/**
	 * Regex segment that matches a brand UUID.
	 */
	const BRAND_ID_PATTERN = '(?P<id>[a-zA-Z0-9\-]+)';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		$id_arg = [
			'id' => [
				'description'       => esc_html__( 'Brand UUID.', 'seo-by-rank-math' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];

		// GET /overview — dashboard payload: summary + per-brand rollup rows.
		register_rest_route(
			$this->namespace,
			'/overview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_overview' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => [
					'refresh' => [
						'description'       => esc_html__( 'Force a refresh from the AI Visibility API.', 'seo-by-rank-math' ),
						'type'              => 'boolean',
						'required'          => false,
						'default'           => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
					'search'  => [
						'description'       => esc_html__( 'Filter brands by name or URL.', 'seo-by-rank-math' ),
						'type'              => 'string',
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		// GET /brands/{id} — single brand identity (incl. description).
		register_rest_route(
			$this->namespace,
			'/brands/' . self::BRAND_ID_PATTERN,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_brand' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => $id_arg,
			]
		);

		// POST /brands — create a new brand.
		register_rest_route(
			$this->namespace,
			'/brands',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_brand' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => [
					'name'        => [
						'description'       => esc_html__( 'Brand name.', 'seo-by-rank-math' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'url'         => [
						'description'       => esc_html__( 'Brand website URL or domain.', 'seo-by-rank-math' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'esc_url_raw',
					],
					'description' => [
						'description'       => esc_html__( 'Short description of the brand or product.', 'seo-by-rank-math' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					],
					'locale'      => [
						'description'       => esc_html__( 'ISO 3166-1 alpha-2 country code (e.g. "US", "HU").', 'seo-by-rank-math' ),
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		// PUT/PATCH /brands/{id} — update brand fields and/or status.
		register_rest_route(
			$this->namespace,
			'/brands/' . self::BRAND_ID_PATTERN,
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_brand' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => array_merge(
					$id_arg,
					[
						'name'        => [
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						],
						'url'         => [
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'esc_url_raw',
						],
						'description' => [
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_textarea_field',
						],
						'locale'      => [
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						],
						'status'      => [
							'type'              => 'string',
							'required'          => false,
							'enum'              => [ 'active', 'inactive' ],
							'sanitize_callback' => 'sanitize_text_field',
						],
					]
				),
			]
		);

		// GET /brands/{id}/insights — full latest-analysis payload
		// (competitors + transcripts). Cache-first; the only proxy route that
		// may call the upstream analyses/results endpoint.
		register_rest_route(
			$this->namespace,
			'/brands/' . self::BRAND_ID_PATTERN . '/insights',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_insights' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => $id_arg,
			]
		);

		// GET /brands/{id}/queries — list queries for a brand.
		register_rest_route(
			$this->namespace,
			'/brands/' . self::BRAND_ID_PATTERN . '/queries',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_queries' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => $id_arg,
			]
		);

		// PUT /brands/{id}/queries/{qid} — enable/disable a query.
		register_rest_route(
			$this->namespace,
			'/brands/' . self::BRAND_ID_PATTERN . '/queries/(?P<qid>[a-zA-Z0-9\-]+)',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_query' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => array_merge(
					$id_arg,
					[
						'qid'     => [
							'description'       => esc_html__( 'Query UUID.', 'seo-by-rank-math' ),
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						],
						'enabled' => [
							'description' => esc_html__( 'Whether the query is enabled for analysis.', 'seo-by-rank-math' ),
							'type'        => 'boolean',
							'required'    => true,
						],
					]
				),
			]
		);

		// POST /brands/{id}/generate-queries — regenerate baseline queries.
		register_rest_route(
			$this->namespace,
			'/brands/' . self::BRAND_ID_PATTERN . '/generate-queries',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'generate_queries' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
				'args'                => $id_arg,
			]
		);
	}

	/**
	 * Dashboard payload (summary + rollup rows) — cache-first with SWR.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_overview( $request ) {
		$force  = (bool) $request->get_param( 'refresh' );
		$search = (string) $request->get_param( 'search' );
		$cached = Cache::get_dashboard();

		if ( null !== $cached && ! $force ) {
			if ( Cache::is_dashboard_fresh() ) {
				return $this->overview_response( $cached, false, $search );
			}

			// Stale-while-revalidate: serve stale immediately; the client
			// fires a background `refresh=1` request on seeing `is_stale`.
			return $this->overview_response( $cached, true, $search );
		}

		$result = $this->remote_request( 'GET', '/api/v1/overview' );

		if ( is_wp_error( $result ) ) {
			// Serve stale cache on upstream failure rather than erroring out.
			if ( null !== $cached ) {
				return $this->overview_response( $cached, true, $search );
			}

			return $result;
		}

		$summary = isset( $result['summary'] ) ? (array) $result['summary'] : [];
		$brands  = [];

		$raw_brands = isset( $result['brands']['data'] ) ? (array) $result['brands']['data'] : [];
		foreach ( $raw_brands as $brand ) {
			$brands[] = $this->map_overview_brand( $brand );
		}

		Cache::set_dashboard( $summary, $brands );

		return $this->overview_response( Cache::get_dashboard(), false, $search );
	}

	/**
	 * Map an /overview brand item (API shape) to the UI row shape.
	 *
	 * Note: `description` is not returned by /overview — it is fetched
	 * lazily and cached per brand via `get_brand()`.
	 *
	 * @param array $brand Raw brand item.
	 *
	 * @return array
	 */
	private function map_overview_brand( $brand ) {
		return [
			'id'              => $brand['uuid'] ?? '',
			'name'            => $brand['name'] ?? '',
			'url'             => $brand['url'] ?? '',
			'locale'          => $brand['country_code'] ?? null,
			'status'          => ! empty( $brand['active'] ) ? 'active' : 'inactive',
			'score'           => $brand['ai_visibility_score'] ?? null,
			'rank'            => $brand['rank'] ?? null,
			'avg_sentiment'   => $brand['avg_sentiment'] ?? null,
			'mentions'        => $brand['mentions'] ?? null,
			'citations'       => $brand['citations'] ?? null,
			'last_analyzed'   => $brand['last_analyzed_at'] ?? null,
			'created_at'      => $brand['created_at'] ?? null,
			'analysis_status' => $brand['last_analysis_status'] ?? null,
			'next_scheduled'  => $brand['next_scheduled_at'] ?? null,
		];
	}

	/**
	 * Build the dashboard response envelope, applying the optional search
	 * filter (name/URL) against the stored rows. The filter never affects
	 * what is cached — only what is returned.
	 *
	 * @param array  $data     Cached dashboard payload.
	 * @param bool   $is_stale Whether the payload is past its TTL.
	 * @param string $search   Optional name/URL filter.
	 *
	 * @return \WP_REST_Response
	 */
	private function overview_response( $data, $is_stale, $search = '' ) {
		$brands = isset( $data['brands'] ) ? $data['brands'] : [];

		if ( '' !== $search ) {
			$brands = array_values(
				array_filter(
					$brands,
					function ( $row ) use ( $search ) {
						return false !== stripos( $row['name'] ?? '', $search )
							|| false !== stripos( $row['url'] ?? '', $search );
					}
				)
			);
		}

		return $this->success(
			[
				'summary'  => isset( $data['summary'] ) ? $data['summary'] : [],
				'brands'   => $brands,
				'is_stale' => (bool) $is_stale,
			]
		);
	}

	/**
	 * Get single brand identity (incl. description) — cache-first.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_brand( $request ) {
		$uuid   = sanitize_text_field( $request->get_param( 'id' ) );
		$cached = Cache::get_brand( $uuid );

		if ( false !== $cached ) {
			return $this->success( [ 'brand' => $cached ] );
		}

		// /overview omits `description`, so the single-brand endpoint is the
		// canonical source for full identity. Fetched once, then cached.
		$result = $this->remote_request( 'GET', '/api/v1/brands/' . $uuid );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$brand = $this->map_brand( isset( $result['data'] ) ? $result['data'] : $result );
		Cache::set_brand( $uuid, $brand );

		return $this->success( [ 'brand' => $brand ] );
	}

	/**
	 * Create a brand — the 201 response primes the dashboard row, identity
	 * transient, and queries cache (no follow-up reads).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_brand( $request ) {
		$locale = $request->get_param( 'locale' );
		$result = $this->remote_request(
			'POST',
			'/api/v1/brands',
			[
				'name'         => sanitize_text_field( (string) $request->get_param( 'name' ) ),
				'url'          => esc_url_raw( (string) $request->get_param( 'url' ) ),
				'description'  => sanitize_textarea_field( (string) $request->get_param( 'description' ) ),
				'country_code' => $locale ? strtoupper( (string) $locale ) : '',
			]
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$brand = $this->map_brand( $result );
		$uuid  = $brand['id'];

		// Prime caches from the create response. Creation atomically seeds a
		// pending analysis, so the row starts as `pending` (no last_analyzed) —
		// this is what makes it eligible for the first-analysis poller.
		Cache::append_brand_row(
			array_merge(
				$brand,
				[
					'score'           => null,
					'rank'            => null,
					'avg_sentiment'   => null,
					'mentions'        => null,
					'citations'       => null,
					'analysis_status' => 'pending',
					'next_scheduled'  => null,
				]
			)
		);
		Cache::set_brand( $uuid, $brand );

		if ( ! empty( $result['queries'] ) ) {
			Cache::set_queries( $uuid, $this->map_queries( $result['queries'] ) );
		}

		rank_math()->tracking->track_event(
			'AI Visibility Brand Created',
			[
				'locale'    => $brand['locale'] ?? null,
				'interval'  => 'weekly',
				'platforms' => [ 'chatgpt' ],
			]
		);

		return $this->success( [ 'brand' => $brand ] );
	}

	/**
	 * Update brand fields/status — patches the cached row in place
	 * (analysis and queries caches are intentionally kept).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_brand( $request ) {
		$uuid = sanitize_text_field( $request->get_param( 'id' ) );

		$body   = [];
		$fields = [
			'name'        => 'sanitize_text_field',
			'url'         => 'esc_url_raw',
			'description' => 'sanitize_textarea_field',
			'status'      => 'sanitize_text_field',
		];

		foreach ( $fields as $field => $sanitizer ) {
			$value = $request->get_param( $field );
			if ( null !== $value ) {
				$body[ $field ] = call_user_func( $sanitizer, $value );
			}
		}

		$locale = $request->get_param( 'locale' );
		if ( null !== $locale ) {
			$body['country_code'] = strtoupper( sanitize_text_field( $locale ) );
		}

		if ( empty( $body ) ) {
			return $this->error( 'aiv_bad_request', __( 'No fields to update.', 'seo-by-rank-math' ), 400 );
		}

		$result = $this->remote_request( 'PATCH', '/api/v1/brands/' . $uuid, $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$brand = $this->map_brand( isset( $result['data'] ) ? $result['data'] : $result );

		// Write-through: patch the dashboard row, replace the identity transient.
		Cache::patch_brand_row(
			$uuid,
			[
				'name'        => $brand['name'],
				'url'         => $brand['url'],
				'description' => $brand['description'],
				'locale'      => $brand['locale'],
				'status'      => $brand['status'],
			]
		);
		Cache::set_brand( $uuid, $brand );

		$status_changed = isset( $body['status'] );
		rank_math()->tracking->track_event(
			'AI Visibility Brand Updated',
			[
				'fields_changed' => array_keys( $body ),
				'status_changed' => $status_changed,
				'new_status'     => $status_changed ? $brand['status'] : null,
			]
		);

		return $this->success( [ 'brand' => $brand ] );
	}

	/**
	 * Map an upstream brand object to the UI shape.
	 *
	 * @param array $brand Raw brand object.
	 *
	 * @return array
	 */
	private function map_brand( $brand ) {
		return [
			'id'            => $brand['uuid'] ?? '',
			'name'          => $brand['name'] ?? '',
			'url'           => $brand['url'] ?? '',
			'description'   => $brand['description'] ?? '',
			'locale'        => $brand['country_code'] ?? null,
			'status'        => $brand['status'] ?? 'active',
			'last_analyzed' => $brand['last_analyzed_at'] ?? null,
			'created_at'    => $brand['created_at'] ?? null,
		];
	}

	/**
	 * Full latest-analysis payload — cache-first; upstream only on miss or
	 * stale flag. Upstream 404 (no completed analysis) → `{ pending: true }`.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_insights( $request ) {
		$uuid   = sanitize_text_field( $request->get_param( 'id' ) );
		$cached = Cache::get_analysis( $uuid );

		if ( false !== $cached && ! Cache::is_brand_stale( $uuid ) ) {
			return $this->success( [ 'insights' => $cached ] );
		}

		$result = $this->remote_request( 'GET', '/api/v1/brands/' . $uuid . '/analyses/results' );

		if ( is_wp_error( $result ) ) {
			// No completed analysis yet — pending state, never cached.
			if ( 'aiv_not_found' === $result->get_error_code() ) {
				return $this->success( [ 'pending' => true ] );
			}

			// Serve the superseded payload on upstream failure.
			if ( false !== $cached ) {
				return $this->success( [ 'insights' => $cached ] );
			}

			return $result;
		}

		$insights = $this->map_insights( $result );

		Cache::set_analysis( $uuid, $insights );

		rank_math()->tracking->track_event(
			'AI Visibility Insights Fetched',
			[
				'score'              => $insights['score'],
				'rank'               => $insights['rank'],
				'avg_sentiment'      => $insights['avg_sentiment'],
				'mentions'           => $insights['mentions'],
				'citations'          => $insights['citations'],
				'has_competitors'    => ! empty( $insights['competitors'] ),
				'query_result_count' => count( $insights['query_results'] ),
			]
		);

		// Keep the dashboard row's summary in sync (poller success path).
		Cache::patch_brand_row(
			$uuid,
			[
				'score'         => $insights['score'],
				'rank'          => $insights['rank'],
				'avg_sentiment' => $insights['avg_sentiment'],
				'mentions'      => $insights['mentions'],
				'citations'     => $insights['citations'],
				'last_analyzed' => $insights['analysis']['finished_at'],
			]
		);

		return $this->success( [ 'insights' => $insights ] );
	}

	/**
	 * Map the upstream analyses/results payload to the UI shape.
	 *
	 * @param array $result Raw payload.
	 *
	 * @return array
	 */
	private function map_insights( $result ) {
		$analysis = isset( $result['analysis'] ) ? (array) $result['analysis'] : [];

		return [
			'score'         => $result['ai_visibility_score'] ?? null,
			'rank'          => $result['rank'] ?? null,
			'avg_sentiment' => $result['avg_sentiment'] ?? null,
			'mentions'      => $result['mentions'] ?? null,
			'citations'     => $result['citations'] ?? null,
			'analysis'      => [
				'id'               => $analysis['uuid'] ?? null,
				// Map the API status vocabulary to the UI one (`success` → `done`).
				'status'           => isset( $analysis['status'] ) && 'success' === $analysis['status'] ? 'done' : ( $analysis['status'] ?? null ),
				'started_at'       => $analysis['started_at'] ?? null,
				'finished_at'      => $analysis['finished_at'] ?? null,
				'duration_seconds' => $analysis['duration_seconds'] ?? null,
			],
			'competitors'   => array_map(
				function ( $competitor ) {
					return [
						'name'          => $competitor['name'] ?? '',
						'url'           => $competitor['url'] ?? null,
						'mentions'      => $competitor['mentions'] ?? null,
						'avg_sentiment' => $competitor['avg_sentiment'] ?? null,
					];
				},
				isset( $result['competitors'] ) ? (array) $result['competitors'] : []
			),
			'query_results' => array_map(
				function ( $query_result ) {
					return [
						'query_id'        => $query_result['query_uuid'] ?? null,
						'query_text'      => $query_result['query_text'] ?? '',
						'found'           => ! empty( $query_result['found'] ),
						'rank'            => $query_result['rank_among_competitors'] ?? null,
						'sentiment'       => $query_result['sentiment_score'] ?? null,
						'citations'       => $query_result['citations_count'] ?? null,
						'response'        => $query_result['model_response'] ?? '',
						'extraction_data' => $query_result['extraction_data_raw'] ?? '',
					];
				},
				isset( $result['query_results'] ) ? (array) $result['query_results'] : []
			),
		];
	}

	/**
	 * List queries for a brand — cache-first (24h TTL).
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_queries( $request ) {
		$uuid   = sanitize_text_field( $request->get_param( 'id' ) );
		$cached = Cache::get_queries( $uuid );

		if ( null !== $cached ) {
			return $this->success( [ 'queries' => $cached['queries'] ] );
		}

		$result = $this->remote_request( 'GET', '/api/v1/brands/' . $uuid . '/queries?enabled=true' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$queries = $this->map_queries( isset( $result['queries'] ) ? $result['queries'] : [] );
		Cache::set_queries( $uuid, $queries );

		return $this->success( [ 'queries' => $queries ] );
	}

	/**
	 * Enable or disable a query.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_query( $request ) {
		$uuid    = sanitize_text_field( $request->get_param( 'id' ) );
		$qid     = sanitize_text_field( $request->get_param( 'qid' ) );
		$enabled = (bool) $request->get_param( 'enabled' );

		$result = $this->remote_request( 'PATCH', '/api/v1/queries/' . $qid, [ 'enabled' => $enabled ] );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		Cache::patch_query( $uuid, $qid, [ 'enabled' => $enabled ] );

		rank_math()->tracking->track_event(
			'AI Visibility Query Toggled',
			[ 'enabled' => $enabled ]
		);

		return $this->success( [ 'updated' => true ] );
	}

	/**
	 * Regenerate baseline queries — response replaces the cached list.
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function generate_queries( $request ) {
		$uuid = sanitize_text_field( $request->get_param( 'id' ) );

		$result = $this->remote_request( 'POST', '/api/v1/brands/' . $uuid . '/generate-queries' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$queries = $this->map_queries( isset( $result['queries'] ) ? $result['queries'] : [] );
		Cache::set_queries( $uuid, $queries );

		rank_math()->tracking->track_event(
			'AI Visibility Queries Regenerated',
			[ 'query_count' => count( $queries ) ]
		);

		return $this->success( [ 'queries' => $queries ] );
	}

	/**
	 * Map upstream query objects to the UI shape.
	 *
	 * @param array $queries Raw query objects.
	 *
	 * @return array
	 */
	private function map_queries( $queries ) {
		return array_map(
			function ( $query ) {
				return [
					'id'          => $query['uuid'] ?? '',
					'text'        => $query['text'] ?? '',
					'enabled'     => $query['enabled'] ?? true,
					'is_baseline' => ! empty( $query['is_baseline'] ),
					'created_at'  => $query['created_at'] ?? null,
					'updated_at'  => $query['updated_at'] ?? null,
				];
			},
			(array) $queries
		);
	}
}
