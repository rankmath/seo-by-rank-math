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
use MyThemeShop\Helpers\Str;

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
	 * @param bool $force_update Whether to send a request to API to get the new Credits value.
	 */
	public static function get_content_ai_credits( $force_update = false ) {
		$registered = Admin_Helper::get_registration_data();
		if ( empty( $registered ) ) {
			return 0;
		}

		$credits = self::get_credits();
		if ( $credits && ! $force_update ) {
			return $credits;
		}

		$args = [
			'username' => rawurlencode( $registered['username'] ),
			'api_key'  => rawurlencode( $registered['api_key'] ),
			'site_url' => rawurlencode( self::get_home_url() ),
		];

		$url = add_query_arg(
			$args,
			'https://rankmath.com/wp-json/contentai/v1/credits'
		);

		$data = wp_remote_get(
			$url,
			[
				'timeout' => 60,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $data );
		if ( 200 !== $response_code ) {
			return $credits;
		}

		$data = wp_remote_retrieve_body( $data );
		if ( empty( $data ) ) {
			return 0;
		}

		$data    = json_decode( $data, true );
		$credits = ! empty( $data['credits'] ) ? json_decode( $data['credits'], true ) : [];
		$data    = [
			'credits'      => ! empty( $credits['available'] ) ? $credits['available'] - $credits['taken'] : 0,
			'plan'         => ! empty( $data['plan'] ) ? $data['plan'] : 0,
			'refresh_date' => ! empty( $data['refreshDate'] ) ? $data['refreshDate'] : 0,
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
		return ! empty( $credits_data['plan'] ) ? $credits_data['plan'] : '';
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
		$custom_prompts = ! is_array( $saved_prompts ) ? [] : array_map(
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
		];
	}
}
