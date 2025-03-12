<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.71
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Controller;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Rest class.
 */
class Rest extends WP_REST_Controller {

	/**
	 * Registered data.
	 *
	 * @var array|false
	 */
	private $registered;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace  = \RankMath\Rest\Rest_Helper::BASE . '/ca';
		$this->registered = Admin_Helper::get_registration_data();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/researchKeyword',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'research_keyword' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => $this->get_research_keyword_args(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/getCredits',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_credits' ],
				'permission_callback' => [ $this, 'has_permission' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/createPost',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_post' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'content' => [
						'description' => esc_html__( 'The content of the new post.', 'rank-math' ),
						'type'        => 'string',
						'required'    => true,
					],
					'title'   => [
						'description' => esc_html__( 'The title of the new post.', 'rank-math' ),
						'type'        => 'string',
						'required'    => false,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/saveOutput',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_output' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'outputs'    => [
						'description' => esc_html__( 'An array of AI-generated and existing outputs to be saved.', 'rank-math' ),
						'type'        => 'array',
						'required'    => true,
					],
					'endpoint'   => [
						'description' => esc_html__( 'The API endpoint for which the output was generated.', 'rank-math' ),
						'type'        => 'string',
						'required'    => true,
					],
					'isChat'     => [
						'description' => esc_html__( 'Indicates if the request was for the Chat endpoint.', 'rank-math' ),
						'type'        => 'boolean',
						'required'    => false,
					],
					'attributes' => [
						'description' => esc_html__( 'The parameters used to generate the AI output.', 'rank-math' ),
						'type'        => 'object',
						'required'    => false,
					],
					'credits'    => [
						'description' => esc_html__( 'Credit usage details returned by the API.', 'rank-math' ),
						'type'        => 'object',
						'required'    => false,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/deleteOutput',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'delete_output' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'isChat' => [
						'description' => esc_html__( 'Indicates if the request to delete the output was for the Chat endpoint.', 'rank-math' ),
						'type'        => 'boolean',
						'required'    => false,
					],
					'index'  => [
						'description' => esc_html__( 'The output index to delete, applicable only to the Chat endpoint.', 'rank-math' ),
						'type'        => 'integer',
						'required'    => false,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateRecentPrompt',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_recent_prompt' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'prompt' => [
						'description' => esc_html__( 'The selected prompt to be updated in the recent prompts.', 'rank-math' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/updatePrompt',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_prompt' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'prompt' => [
						'description' => esc_html__( 'The prompt data to be saved in the database.', 'rank-math' ),
						'type'        => 'object',
						'required'    => true,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/savePrompts',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_prompts' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'prompts' => [
						'description' => esc_html__( 'A list of prompts received from the API to be saved in the database.', 'rank-math' ),
						'type'        => 'array',
						'required'    => true,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/pingContentAI',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'ping_content_ai' ],
				'permission_callback' => [ $this, 'has_ping_permission' ],
				'args'                => [
					'plan'        => [
						'description' => esc_html__( 'Content AI plan to update in the Database.', 'rank-math' ),
						'type'        => 'string',
						'required'    => true,
					],
					'refreshDate' => [
						'description' => esc_html__( 'Content AI reset date to update in the Database', 'rank-math' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/generateAlt',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'generate_alt' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'attachmentIds' => [
						'description' => esc_html__( 'List of attachment IDs for which to generate alt text.', 'rank-math' ),
						'type'        => 'array',
						'required'    => true,
					],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/updateCredits',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_credits' ],
				'permission_callback' => [ $this, 'has_permission' ],
				'args'                => [
					'attachmentIds' => [
						'description' => esc_html__( 'Credit usage details returned by the API.', 'rank-math' ),
						'type'        => 'object',
						'required'    => true,
					],
				],
			]
		);
	}

	/**
	 * Check API key in request.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool                     Whether the API key matches or not.
	 */
	public function has_ping_permission( WP_REST_Request $request ) {
		if ( empty( $this->registered ) ) {
			return false;
		}

		return $request->get_param( 'apiKey' ) === $this->registered['api_key'] &&
			$request->get_param( 'username' ) === $this->registered['username'];
	}

	/**
	 * Determines if the current user can manage analytics.
	 *
	 * @return true
	 */
	public function has_permission() {
		if ( ! Helper::has_cap( 'content_ai' ) || empty( $this->registered ) ) {
			return new WP_Error(
				'rest_cannot_access',
				__( 'Sorry, only authenticated users can research the keyword.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Get Content AI Credits.
	 *
	 * @return int Credits.
	 */
	public function get_credits() {
		$credits = Helper::get_content_ai_credits( true, true );
		if ( ! empty( $credits['error'] ) ) {
			$error       = $credits['error'];
			$error_texts = Helper::get_content_ai_errors();
			return [
				'error'   => ! empty( $error_texts[ $error ] ) ? wp_specialchars_decode( $error_texts[ $error ], ENT_QUOTES ) : $error,
				'credits' => isset( $credits['credits'] ) ? $credits['credits'] : '',
			];
		}

		return $credits;
	}

	/**
	 * Research a keyword.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function research_keyword( WP_REST_Request $request ) {
		$object_id    = $request->get_param( 'objectID' );
		$country      = $request->get_param( 'country' );
		$keyword      = mb_strtolower( $request->get_param( 'keyword' ) );
		$force_update = $request->get_param( 'forceUpdate' );
		$keyword_data = get_option( 'rank_math_ca_data' );
		$post_type    = 0 === $object_id ? 'page' : get_post_type( $object_id );

		if ( ! in_array( $post_type, (array) Helper::get_settings( 'general.content_ai_post_types' ), true ) ) {
			return [
				'data' => esc_html__( 'Content AI is not enabled on this Post type.', 'rank-math' ),
			];
		}

		if ( ! apply_filters( 'rank_math/content_ai/call_api', true ) ) {
			return [
				'data' => 'show_dummy_data',
			];
		}

		if (
			! $force_update &&
			! empty( $keyword_data ) &&
			! empty( $keyword_data[ $country ] ) &&
			! empty( $keyword_data[ $country ][ $keyword ] )
		) {
			update_post_meta(
				$object_id,
				'rank_math_ca_keyword',
				[
					'keyword' => $keyword,
					'country' => $country,
				]
			);

			return [
				'data'    => $keyword_data[ $country ][ $keyword ],
				'keyword' => $keyword,
			];
		}

		$data = $this->get_researched_data( $keyword, $country, $force_update );
		if ( ! empty( $data['error'] ) ) {
			return $this->get_errored_data( $data['error'] );
		}

		$credits = ! empty( $data['credits'] ) ? $data['credits'] : 0;
		if ( ! empty( $credits ) ) {
			$credits = $credits['available'] - $credits['taken'];
		}

		$data = $data['data']['details'];
		$this->get_recommendations( $data );

		update_post_meta(
			$object_id,
			'rank_math_ca_keyword',
			[
				'keyword' => $keyword,
				'country' => $country,
			]
		);
		$keyword_data[ $country ][ $keyword ] = $data;
		update_option( 'rank_math_ca_data', $keyword_data, false );
		Helper::update_credits( $credits );

		return [
			'data'    => $keyword_data[ $country ][ $keyword ],
			'credits' => $credits,
			'keyword' => $keyword,
		];
	}

	/**
	 * Get the arguments for the researchKeyword route.
	 *
	 * @return array
	 */
	public function get_research_keyword_args() {
		return [
			'keyword'      => [
				'description' => esc_html__( 'The keyword to be researched.', 'rank-math' ),
				'type'        => 'string',
				'required'    => true,
			],
			'country'      => [
				'description' => esc_html__( 'The country for which the keyword should be researched.', 'rank-math' ),
				'type'        => 'string',
				'required'    => true,
			],
			'objectID'     => [
				'description' => esc_html__( 'The ID of the post initiating the keyword research request.', 'rank-math' ),
				'type'        => 'integer',
				'required'    => true,
			],
			'force_update' => [
				'description' => esc_html__( 'If true, forces a fresh research request.', 'rank-math' ),
				'type'        => 'boolean',
				'required'    => false,
			],
		];
	}

	/**
	 * Create a new Post from Content AI Page.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_post( WP_REST_Request $request ) {
		$content       = $request->get_param( 'content' );
		$title         = $request->get_param( 'title' );
		$title         = $title ? $title : 'Content AI Post';
		$blocks        = parse_blocks( $content );
		$current_block = ! empty( $blocks ) ? current( $blocks ) : '';
		if (
			! empty( $current_block ) &&
			$current_block['blockName'] === 'core/heading' &&
			$current_block['attrs']['level'] === 1
		) {
			$title = wp_strip_all_tags( $current_block['innerHTML'] );
		}

		$post_id = wp_insert_post(
			[
				'post_title'   => $title,
				'post_content' => $content,
			]
		);

		return wp_specialchars_decode( add_query_arg( 'tab', 'content-ai', get_edit_post_link( $post_id ) ) );
	}

	/**
	 * Save the API output.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_output( WP_REST_Request $request ) {
		$outputs      = $request->get_param( 'outputs' );
		$endpoint     = $request->get_param( 'endpoint' );
		$is_chat      = $request->get_param( 'isChat' );
		$attributes   = $request->get_param( 'attributes' );
		$credits_data = $request->get_param( 'credits' );

		if ( ! empty( $credits_data ) ) {
			$credits = ! empty( $credits_data['credits'] ) ? $credits_data['credits'] : [];
			$data    = [
				'credits'      => ! empty( $credits['available'] ) ? $credits['available'] - $credits['taken'] : 0,
				'plan'         => ! empty( $credits_data['plan'] ) ? $credits_data['plan'] : '',
				'refresh_date' => ! empty( $credits_data['refreshDate'] ) ? $credits_data['refreshDate'] : '',
			];

			Helper::update_credits( $data );
		}

		if ( $is_chat ) {
			Helper::update_chats( current( $outputs ), end( $attributes['messages'] ), $attributes['session'], $attributes['isNew'], $attributes['regenerate'] );
			return true;
		}

		return Helper::update_outputs( $endpoint, $outputs );
	}

	/**
	 * Delete the API output.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_output( WP_REST_Request $request ) {
		$is_chat = $request->get_param( 'isChat' );
		if ( $is_chat ) {
			return Helper::delete_chats( $request->get_param( 'index' ) );
		}

		return Helper::delete_outputs();
	}

	/**
	 * Update the Prompts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_prompt( WP_REST_Request $request ) {
		$prompt = $request->get_param( 'prompt' );

		if ( is_string( $prompt ) ) {
			return Helper::delete_prompt( $prompt );
		}

		return Helper::update_prompts( $prompt );
	}

	/**
	 * Save the Prompts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_prompts( WP_REST_Request $request ) {
		$prompts = $request->get_param( 'prompts' );
		if ( empty( $prompts ) ) {
			return false;
		}

		return Helper::save_default_prompts( $prompts );
	}

	/**
	 * Update the Recent Prompts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_recent_prompt( WP_REST_Request $request ) {
		$prompt = $request->get_param( 'prompt' );
		return Helper::update_recent_prompts( $prompt );
	}

	/**
	 * Endpoing to update the AI plan and credits.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function ping_content_ai( WP_REST_Request $request ) {
		$credits = ! empty( $request->get_param( 'credits' ) ) ? json_decode( $request->get_param( 'credits' ), true ) : [];
		$data    = [
			'credits'      => ! empty( $credits['available'] ) ? $credits['available'] - $credits['taken'] : 0,
			'plan'         => $request->get_param( 'plan' ),
			'refresh_date' => $request->get_param( 'refreshDate' ),
		];

		Helper::update_credits( $data );

		return true;
	}

	/**
	 * Endpoint to generate Image Alt.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function generate_alt( WP_REST_Request $request ) {
		$ids = $request->get_param( 'attachmentIds' );
		if ( empty( $ids ) ) {
			return false;
		}

		do_action( 'rank_math/content_ai/generate_alt', $ids );

		return true;
	}

	/**
	 * Endpoint to Update the credits data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_credits( WP_REST_Request $request ) {
		$credits = $request->get_param( 'credits' );
		Helper::update_credits( $credits );
		return true;
	}

	/**
	 * Get data from the API.
	 *
	 * @param string $keyword      Researched keyword.
	 * @param string $country      Researched country.
	 * @param bool   $force_update Whether to force update the researched data.
	 *
	 * @return array
	 */
	private function get_researched_data( $keyword, $country, $force_update = false ) {
		$args = [
			'username' => rawurlencode( $this->registered['username'] ),
			'api_key'  => rawurlencode( $this->registered['api_key'] ),
			'keyword'  => rawurlencode( $keyword ),
			'site_url' => rawurlencode( Helper::get_home_url() ),
			'new_api'  => 1,
		];

		if ( 'all' !== $country ) {
			$args['locale'] = rawurlencode( $country );
		}

		if ( $force_update ) {
			$args['force_refresh'] = 1;
		}

		$url = add_query_arg(
			$args,
			CONTENT_AI_URL . '/ai/research'
		);

		$data = wp_remote_get(
			$url,
			[
				'timeout' => 60,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $data );
		if ( 200 !== $response_code ) {
			return [
				'error' => 410 !== $response_code ? $data['response']['message'] : wp_kses_post(
					sprintf(
						// Translators: link to the update page.
						__( 'There is a new version of Content AI available! %s the Rank Math SEO plugin to use this feature.', 'rank-math' ),
						'<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '">' . __( 'Please update', 'rank-math' ) . '</a>'
					)
				),
			];
		}

		$data = wp_remote_retrieve_body( $data );
		$data = json_decode( $data, true );

		if ( empty( $data['error'] ) && empty( $data['data']['details'] ) ) {
			return [
				'error' => esc_html__( 'No data found for the researched keyword.', 'rank-math' ),
			];
		}

		return $data;
	}

	/**
	 * Get errored data.
	 *
	 * @param array $error Error data received from the API.
	 *
	 * @return array
	 */
	private function get_errored_data( $error ) {
		if ( empty( $error['code'] ) ) {
			return [
				'data' => $error,
			];
		}

		if ( 'invalid_domain' === $error['code'] ) {
			return [
				'data' => esc_html__( 'This feature is not available on the localhost.', 'rank-math' ),
			];
		}

		if ( 'domain_limit_reached' === $error['code'] ) {
			return [
				'data' => esc_html__( 'You have used all the free credits which are allowed to this domain.', 'rank-math' ),
			];
		}

		return [
			'data'    => '',
			'credits' => $error['code'],
		];
	}

	/**
	 * Get the Recommendations data.
	 *
	 * @param array $data Researched data.
	 */
	private function get_recommendations( &$data ) {
		foreach ( $data['recommendations'] as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			$data['recommendations'][ $key ]['total'] = array_sum( $value );
		}
	}
}
