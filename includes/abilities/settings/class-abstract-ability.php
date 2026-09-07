<?php
/**
 * Abstract base class for Settings abilities.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Helper;
use RankMath\Abilities\Ability_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Shared infrastructure for all Settings abilities.
 *
 * Provides the constructor, check_permissions(), option-reader helpers,
 * apply_toggle_fields(), save_and_reset(), build_meta(), and write_success_schema()
 * so concrete classes only need to implement register() and execute().
 */
abstract class Abstract_Ability implements Ability_Interface {

	/**
	 * Allowed robots meta directive values — shared across abilities.
	 */
	const ROBOTS_DIRECTIVES = [ 'index', 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ];

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	protected $category;

	/**
	 * Shared meta args.
	 *
	 * @var array
	 */
	protected $shared_meta;

	/**
	 * WordPress capability required to execute this ability.
	 *
	 * @var string
	 */
	private $required_capability;

	/**
	 * Constructor.
	 *
	 * @param string $category            Ability category slug.
	 * @param array  $shared_meta         Shared meta args.
	 * @param string $required_capability WP capability checked in check_permissions().
	 */
	public function __construct( string $category, array $shared_meta, string $required_capability ) {
		$this->category            = $category;
		$this->shared_meta         = $shared_meta;
		$this->required_capability = $required_capability;
	}

	/**
	 * Check if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( $this->required_capability );
	}

	/**
	 * Current raw general option.
	 *
	 * @return array
	 */
	protected function get_general_option(): array {
		return (array) get_option( 'rank-math-options-general', [] );
	}

	/**
	 * Current raw titles option.
	 *
	 * @return array
	 */
	protected function get_titles_option(): array {
		return (array) get_option( 'rank-math-options-titles', [] );
	}

	/**
	 * Currently active module slugs.
	 *
	 * @return array<int, string>
	 */
	protected function get_active_modules(): array {
		return (array) get_option( 'rank_math_modules', [] );
	}

	/**
	 * Accessible post type slugs.
	 *
	 * @return array<int, string>
	 */
	protected function get_accessible_post_types(): array {
		return array_keys( Helper::get_accessible_post_types() );
	}

	/**
	 * Apply boolean-to-on/off toggle fields from input onto an option array.
	 *
	 * @param array    $option Target option array (modified in-place).
	 * @param array    $input  Ability input.
	 * @param string[] $fields Field names to apply.
	 * @return void
	 */
	protected function apply_toggle_fields( array &$option, array $input, array $fields ): void {
		foreach ( $fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$option[ $field ] = $input[ $field ] ? 'on' : 'off';
			}
		}
	}

	/**
	 * Persist settings and flush the in-memory cache.
	 *
	 * @param array|null $general  Updated general section, or null to leave untouched.
	 * @param array|null $titles   Updated titles section, or null to leave untouched.
	 * @param array|null $sitemap  Updated sitemap section, or null to leave untouched.
	 * @return void
	 */
	protected function save_and_reset( ?array $general, ?array $titles, ?array $sitemap ): void {
		Helper::update_all_settings( $general, $titles, $sitemap );
		rank_math()->settings->reset();
	}

	/**
	 * Build the meta array for wp_register_ability(), merging in annotations.
	 *
	 * @param bool $read_only Whether this ability only reads data.
	 * @return array
	 */
	protected function build_meta( bool $read_only ): array {
		return array_merge(
			$this->shared_meta,
			[
				'annotations' => [
					'readonly'    => $read_only,
					'destructive' => false,
					'idempotent'  => true,
				],
			]
		);
	}

	/**
	 * JSON schema fragment common to all write-ability success responses.
	 *
	 * @param bool   $with_error     Include the error property.
	 * @param string $updated_description Description for the updated field.
	 * @return array
	 */
	protected function write_success_schema( bool $with_error = false, string $updated_description = 'List of field names that were updated.' ): array {
		$schema = [
			'type'       => 'object',
			'properties' => [
				'saved'   => [
					'type'        => 'boolean',
					'description' => 'Whether the update was saved.',
				],
				'updated' => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => $updated_description,
				],
			],
		];

		if ( $with_error ) {
			$schema['properties']['error'] = [
				'type'        => 'object',
				'description' => 'Present only when the update failed validation.',
				'properties'  => [
					'code'    => [ 'type' => 'string' ],
					'message' => [ 'type' => 'string' ],
				],
			];
		}

		return $schema;
	}
}
