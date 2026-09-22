<?php
/**
 * AI platform registry.
 *
 * @since      1.0.275
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility;

use WP_Error;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Platforms class.
 */
final class Platforms {

	/**
	 * Plan that unlocks every supported platform.
	 */
	const UNLIMITED_PLAN = 'expert';

	/**
	 * Platform definitions. Key order drives the UI column split.
	 *
	 * @var array<string, array{label: string, enabled: bool}>
	 */
	private const PLATFORMS = [
		'chatgpt'    => [
			'label'   => 'ChatGPT',
			'enabled' => true,
		],
		'gemini'     => [
			'label'   => 'Google Gemini',
			'enabled' => true,
		],
		'perplexity' => [
			'label'   => 'Perplexity',
			'enabled' => false,
		],
		'claude'     => [
			'label'   => 'Claude',
			'enabled' => false,
		],
	];

	/**
	 * Every known platform, keyed by ID.
	 *
	 * @return array<string, array{label: string, enabled: bool}>
	 */
	public static function all() {
		return self::PLATFORMS;
	}

	/**
	 * Platform IDs available for selection.
	 *
	 * @return string[]
	 */
	public static function supported() {
		return array_keys(
			array_filter(
				self::PLATFORMS,
				function ( $platform ) {
					return ! empty( $platform['enabled'] );
				}
			)
		);
	}

	/**
	 * Platforms the current plan may track per brand.
	 *
	 * @return int
	 */
	public static function max_selectable() {
		return self::UNLIMITED_PLAN === Helper::get_content_ai_plan()
			? count( self::supported() )
			: 1;
	}

	/**
	 * Supported IDs as a quoted list, e.g. `"chatgpt", "gemini"`.
	 *
	 * @return string
	 */
	public static function supported_as_string() {
		return implode(
			', ',
			array_map(
				function ( $id ) {
					return '"' . $id . '"';
				},
				self::supported()
			)
		);
	}

	/**
	 * REST route arg definition for a `platforms` param.
	 *
	 * @param bool $required Whether the param is mandatory.
	 *
	 * @return array
	 */
	public static function rest_arg( $required ) {
		return [
			'description'       => sprintf(
				/* translators: %s: supported platform IDs. */
				esc_html__( 'AI platforms to track for this brand. Supported values: %s. Tracking more than one platform per brand requires the Expert plan.', 'seo-by-rank-math' ),
				self::supported_as_string()
			),
			'type'              => 'array',
			'minItems'          => 1,
			'items'             => [
				'type' => 'string',
				'enum' => self::supported(),
			],
			'required'          => $required,
			'validate_callback' => [ self::class, 'validate' ],
			'sanitize_callback' => [ self::class, 'sanitize' ],
		];
	}

	/**
	 * Sanitize, dedupe and drop unsupported IDs.
	 *
	 * @param mixed $value Raw platforms value.
	 *
	 * @return string[]
	 */
	public static function sanitize( $value ) {
		return array_values(
			array_unique(
				array_intersect(
					array_map( 'sanitize_text_field', (array) $value ),
					self::supported()
				)
			)
		);
	}

	/**
	 * Validate against the registry and the plan limit.
	 *
	 * @param mixed $value Raw platforms value.
	 *
	 * @return true|WP_Error
	 */
	public static function validate( $value ) {
		if ( null === $value || '' === $value || [] === $value ) {
			return new WP_Error(
				'aiv_bad_request',
				sprintf(
					/* translators: %s: supported platform IDs. */
					esc_html__( 'Please specify which AI platforms to track. Supported values: %s.', 'seo-by-rank-math' ),
					self::supported_as_string()
				),
				[ 'status' => 400 ]
			);
		}

		$platforms = self::sanitize( $value );

		if ( empty( $platforms ) ) {
			return new WP_Error(
				'aiv_bad_request',
				sprintf(
					/* translators: %s: supported platform IDs. */
					esc_html__( 'No valid AI platforms were provided. Supported values: %s.', 'seo-by-rank-math' ),
					self::supported_as_string()
				),
				[ 'status' => 400 ]
			);
		}

		$max = self::max_selectable();
		if ( count( $platforms ) > $max ) {
			return new WP_Error(
				'aiv_plan_limit',
				sprintf(
					/* translators: 1: platforms allowed on the current plan, 2: supported platform IDs. */
					esc_html__( 'Your plan allows tracking %1$d AI platform(s) per brand — upgrade to the Expert plan to track more. Supported values: %2$s.', 'seo-by-rank-math' ),
					$max,
					self::supported_as_string()
				),
				[ 'status' => 403 ]
			);
		}

		return true;
	}
}
