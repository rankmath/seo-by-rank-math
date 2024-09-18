<?php
/**
 * The Content_AI helpers.
 *
 * @since      1.0.112
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Admin\Admin_Helper;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Content_AI class.
 */
trait Content_AI {
	/**
	 * Content AI Outputs key.
	 *
	 * @var boolean
	 */
	private static $output_key = 'rank_math_content_ai_outputs';

	/**
	 * Content AI Chats key.
	 *
	 * @var boolean
	 */
	private static $chat_key = 'rank_math_content_ai_chats';

	/**
	 * Content AI Recent Prompts key.
	 *
	 * @var boolean
	 */
	private static $recent_prompt_key = 'rank_math_content_ai_recent_prompts';

	/**
	 * Content AI Prompts key.
	 *
	 * @var boolean
	 */
	private static $prompt_key = 'rank_math_content_ai_prompts';

	/**
	 * Content AI Prompts key.
	 *
	 * @var boolean
	 */
	private static $credits_key = 'rank_math_ca_credits';

	/**
	 * Get the Content AI Credits.
	 *
	 * @param bool $force_update       Whether to send a request to API to get the new Credits value.
	 * @param bool $return_error       Whether to return error when request fails.
	 * @param bool $migration_complete Whether the request was send after migrating the user.
	 */
	public static function get_content_ai_credits( $force_update = false, $return_error = false, $migration_complete = false ) {
		$registered = Admin_Helper::get_registration_data();
		if ( empty( $registered ) ) {
			return 0;
		}

		$transient = 'rank_math_content_ai_requested';
		$credits   = self::get_credits();
		if ( ! $force_update || ( get_site_transient( $transient ) && ! $migration_complete ) ) {
			return $credits;
		}

		set_site_transient( $transient, true, 20 ); // Set transient for 20 seconds.

		$args = [
			'username'       => rawurlencode( $registered['username'] ),
			'api_key'        => rawurlencode( $registered['api_key'] ),
			'site_url'       => rawurlencode( self::get_home_url() ),
			'embedWallet'    => 'false',
			'plugin_version' => rawurlencode( rank_math()->version ),
		];

		$url = add_query_arg(
			$args,
			CONTENT_AI_URL . '/sites/wallet'
		);

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 60,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 404 === $response_code && ! $migration_complete ) {
			return self::maybe_migrate_user( $response );
		}

		$is_error = self::is_content_ai_error( $response, $response_code );
		if ( $is_error ) {

			if ( in_array( $is_error, [ 'domain_limit_reached', 'account_limit_reached' ], true ) ) {
				$credits = 0;
				self::update_credits( 0 );
			}

			return ! $return_error ? $credits : [
				'credits' => $credits,
				'error'   => $is_error,
			];
		}

		$data = wp_remote_retrieve_body( $response );
		if ( empty( $data ) ) {
			return 0;
		}

		$data = json_decode( $data, true );
		$data = [
			'credits'      => intval( $data['availableCredits'] ?? 0 ),
			'plan'         => $data['plan'] ?? '',
			'refresh_date' => $data['nextResetDate'] ?? '',
		];

		self::update_credits( $data );

		return $data['credits'];
	}

	/**
	 * Function to get Content AI Credits.
	 *
	 * @return int Credits data.
	 */
	public static function get_credits() {
		$credits_data = get_option( self::$credits_key, [] );
		return ! empty( $credits_data['credits'] ) ? $credits_data['credits'] : 0;
	}

	/**
	 * Function to get Content AI Plan.
	 *
	 * @return string Content AI Plan.
	 */
	public static function get_content_ai_plan() {
		$credits_data = get_option( self::$credits_key, [] );
		return ! empty( $credits_data['plan'] ) ? strtolower( $credits_data['plan'] ) : '';
	}

	/**
	 * Function to get Content AI Refresh date.
	 *
	 * @return int Content AI Refresh date.
	 */
	public static function get_content_ai_refresh_date() {
		$credits_data = get_option( self::$credits_key, [] );
		return ! empty( $credits_data['refresh_date'] ) ? $credits_data['refresh_date'] : '';
	}

	/**
	 * Function to update Content AI Credits.
	 *
	 * @param int $credits Credits data.
	 */
	public static function update_credits( $credits ) {
		if ( is_array( $credits ) ) {
			$credits['refresh_date'] = ! empty( $credits['refresh_date'] ) && ! is_int( $credits['refresh_date'] ) ? strtotime( $credits['refresh_date'] ) : $credits['refresh_date'];
			update_option( self::$credits_key, $credits );
			return;
		}

		$credits_data = get_option( self::$credits_key, [] );
		if ( ! is_array( $credits_data ) ) {
			$credits_data = [ 'credits' => $credits_data ];
		}

		$credits_data['credits'] = max( 0, $credits );
		update_option( self::$credits_key, $credits_data );
	}

	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @return string Default Schema Type.
	 */
	public static function get_outputs() {
		return get_option( self::$output_key, [] );
	}

	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @return string Default Schema Type.
	 */
	public static function get_chats() {
		return array_values( get_option( self::$chat_key, [] ) );
	}

	/**
	 * Function to get Recent prompts used in Content AI.
	 */
	public static function get_recent_prompts() {
		return get_option( self::$recent_prompt_key, [] );
	}

	/**
	 * Function to get prompts used in Content AI.
	 */
	public static function get_prompts() {
		return get_option( self::$prompt_key, [] );
	}

	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @return string Default Schema Type.
	 */
	public static function delete_outputs() {
		return delete_option( self::$output_key );
	}

	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $output   API output.
	 *
	 * @return array
	 */
	public static function update_outputs( $endpoint, $output ) {
		$outputs = self::get_outputs();

		$output = array_map(
			function( $item ) use ( $endpoint ) {
				return [
					'key'    => $endpoint,
					'output' => $item,
				];
			},
			$output
		);

		$output  = isset( $output['faqs'] ) ? [ current( $output ) ] : $output;
		$outputs = array_merge( $output, $outputs );
		$outputs = array_slice( $outputs, 0, 50 );
		update_option( self::$output_key, $outputs, false );

		return self::get_outputs();
	}

	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @param array   $answer          API endpoint.
	 * @param array   $question        API output.
	 * @param int     $session         Chat session.
	 * @param boolean $is_new          Whether its a new chat.
	 * @param boolean $is_regenerating Is regenerating the Chat message.
	 *
	 * @return void
	 */
	public static function update_chats( $answer, $question, $session = 0, $is_new = false, $is_regenerating = false ) {
		$chats = self::get_chats();

		$data = [
			[
				'role'    => 'assistant',
				'content' => $answer,
			],
			[
				'role'    => 'user',
				'content' => $question['content'],
			],
		];

		if ( $is_new ) {
			array_unshift( $chats, $data );
		} else {
			if ( ! isset( $chats[ $session ] ) ) {
				$chats[ $session ] = [];
			}

			if ( $is_regenerating ) {
				unset( $chats[ $session ][0], $chats[ $session ][1] );
			}

			$chats[ $session ] = array_merge(
				$data,
				$chats[ $session ]
			);
		}

		$chats = array_slice( $chats, 0, 50 );

		update_option( self::$chat_key, array_values( $chats ), false );
	}

	/**
	 * Function to update the Recent prompts data.
	 *
	 * @param string $prompt Prompt name.
	 *
	 * @return boolean
	 */
	public static function update_recent_prompts( $prompt ) {
		$prompts = self::get_recent_prompts();
		array_unshift( $prompts, $prompt );
		$prompts = array_slice( array_filter( array_unique( $prompts ) ), 0, 10 );
		return update_option( self::$recent_prompt_key, $prompts, false );
	}

	/**
	 * Function to save the default prompts data.
	 *
	 * @param array $prompts Prompt data.
	 *
	 * @return array
	 */
	public static function save_default_prompts( $prompts ) {
		$saved_prompts  = self::get_prompts();
		$custom_prompts = ! is_array( $saved_prompts ) || ! empty( $saved_prompts['error'] ) ? [] : array_map(
			function( $prompt ) {
				return $prompt['PromptCategory'] === 'custom' ? $prompt : false;
			},
			$saved_prompts
		);

		if ( ! empty( $custom_prompts ) ) {
			$custom_prompts = array_values( array_filter( $custom_prompts ) );
		}

		$prompts = array_merge( $prompts, $custom_prompts );
		update_option( self::$prompt_key, $prompts );

		return $prompts;
	}

	/**
	 * Function to update the prompts data.
	 *
	 * @param string $prompt Prompt name.
	 *
	 * @return boolean
	 */
	public static function update_prompts( $prompt ) {
		$prompts = self::get_prompts();

		$prompts[] = $prompt;
		update_option( self::$prompt_key, $prompts, false );

		return self::get_prompts();
	}

	/**
	 * Function to delete the prompts.
	 *
	 * @param string $prompt_name Prompt name.
	 *
	 * @return array
	 */
	public static function delete_prompt( $prompt_name ) {
		$prompts = self::get_prompts();
		$prompts = array_map(
			function ( $elem ) use ( $prompt_name ) {
				return $elem['PromptName'] !== $prompt_name ? $elem : false;
			},
			$prompts
		);

		update_option( self::$prompt_key, array_filter( $prompts ), false );

		return self::get_prompts();
	}

	/**
	 * Function to get Default Schema type by post_type.
	 *
	 * @param string $index Group index key.
	 *
	 * @return string Default Schema Type.
	 */
	public static function delete_chats( $index ) {
		$chats = self::get_chats();
		unset( $chats[ $index ] );

		return update_option( self::$chat_key, $chats, false );
	}

	/**
	 * Function to get default language based on the site language.
	 *
	 * @return string Default language.
	 */
	public static function content_ai_default_language() {
		$locale    = get_locale();
		$languages = array_filter(
			[
				'Spanish'    => Str::starts_with( 'es_', $locale ),
				'French'     => Str::starts_with( 'fr_', $locale ),
				'German'     => 'de_DE_formal' === $locale,
				'Italian'    => Str::starts_with( 'it_', $locale ),
				'Dutch'      => Str::starts_with( 'de_', $locale ),
				'Portuguese' => Str::starts_with( 'pt_', $locale ),
				'Russian'    => Str::starts_with( 'ru_', $locale ),
				'Chinese'    => Str::starts_with( 'zh_', $locale ),
				'Korean'     => Str::starts_with( 'ko_', $locale ),
				'UK English' => 'en_GB' === $locale,
				'Japanese'   => 'ja' === $locale,
				'Bulgarian'  => 'bg_BG' === $locale,
				'Czech'      => 'cs_CZ' === $locale,
				'Danish'     => 'da_DK' === $locale,
				'Estonian'   => 'et' === $locale,
				'Finnish'    => 'fi' === $locale,
				'Greek'      => 'el' === $locale,
				'Hebrew'     => 'he_IL' === $locale,
				'Hungarian'  => 'hu_HU' === $locale,
				'Indonesian' => 'id_ID' === $locale,
				'Latvian'    => 'lv' === $locale,
				'Lithuanian' => 'lt_LT' === $locale,
				'Norwegian'  => in_array( $locale, [ 'nb_NO', 'nn_NO' ], true ),
				'Polish'     => 'pl_PL' === $locale,
				'Romanian'   => 'ro_RO' === $locale,
				'Slovak'     => 'sk_SK' === $locale,
				'Slovenian'  => 'sl_SI' === $locale,
				'Swedish'    => 'sv_SE' === $locale,
			]
		);

		return ! empty( $languages ) ? current( array_keys( $languages ) ) : 'US English';
	}

	/**
	 * Function to get different error codes we get from the API
	 *
	 * @return array Array of error codes with messages.
	 */
	public static function get_content_ai_errors() {
		return [
			'not_connected'          => esc_html__( 'Please connect your account to use the Content AI.', 'rank-math' ),
			'plugin_update_required' => esc_html__( 'Please update the Rank Math SEO plugin to the latest version to use this feature.', 'rank-math' ),
			'upgrade_required'       => esc_html__( 'This feature is only available for Content AI subscribers.', 'rank-math' ),
			'rate_limit_exceeded'    => esc_html__( 'Oops! Too many requests in a short time. Please try again after some time.', 'rank-math' ),
			'domain_limit_reached'   => esc_html__( 'You\'ve used up all available credits for this domain.', 'rank-math' ),
			'account_limit_reached'  => esc_html__( 'You\'ve used up all available credits from the connected account.', 'rank-math' ),
			'content_filter'         => esc_html__( 'Please revise the entered values in the fields as they are not secure. Make the required adjustments and try again.', 'rank-math' ),
			'api_content_filter'     => esc_html__( 'The output was stopped as it was identified as potentially unsafe by the content filter.', 'rank-math' ),
			'could_not_generate'     => esc_html__( 'Could not generate. Please try again later.', 'rank-math' ),
			'invalid_key'            => esc_html__( 'Invalid API key. Please check your API key or reconnect the site and try again.', 'rank-math' ),
			'not_found'              => esc_html__( 'User wallet not found.', 'rank-math' ),
		];
	}

	/**
	 * User migration request.
	 */
	public static function migrate_user_to_nest_js() {
		$registered = Admin_Helper::get_registration_data();
		if ( empty( $registered ) || empty( $registered['username'] ) ) {
			return;
		}

		$res = wp_remote_post(
			CONTENT_AI_URL . '/migrate',
			[
				'headers' => [
					'Content-type' => 'application/json',
				],
				'body'    => wp_json_encode( [ 'username' => $registered['username'] ] ),
			]
		);

		$res_code = wp_remote_retrieve_response_code( $res );
		if ( is_wp_error( $res ) || 400 <= $res_code ) {
			return false;
		}

		$data             = json_decode( wp_remote_retrieve_body( $res ), true );
		$migration_status = $data['status'] ?? '';

		return in_array( $migration_status, [ 'added', 'migration_not_needed' ], true ) ? 'completed' : $migration_status;
	}

	/**
	 * Function to return the error message.
	 *
	 * @param array $response      API response.
	 * @param int   $response_code API response code.
	 */
	public static function is_content_ai_error( $response, $response_code ) {
		$data = wp_remote_retrieve_body( $response );
		$data = ! empty( $data ) ? json_decode( $data, true ) : [];
		if ( is_wp_error( $response ) || 200 !== $response_code || empty( $data ) ) {
			return ! empty( $data['err_key'] ) ? $data['err_key'] : 'could_not_generate';
		}

		return ! empty( $data['error'] ) ? $data['error']['code'] : false;
	}

	/**
	 * Migrate user depending on the error received in the response
	 *
	 * @param array $response API response.
	 */
	private static function maybe_migrate_user( $response ) {
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['err_key'] ) || 'not_found' !== $data['err_key'] ) {
			return;
		}

		$status = self::migrate_user_to_nest_js();
		if ( 'completed' === $status ) {
			return self::get_content_ai_credits( true, false, true );
		}
	}
}
