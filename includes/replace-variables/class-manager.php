<?php
/**
 * Variable replacer.
 *
 * Replace '%variables%' in strings based on context.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Replace_Variables;

use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Manager class.
 */
class Manager extends Post_Variables {

	/**
	 * Removed non replaced variables.
	 *
	 * @var bool
	 */
	public $remove_non_replaced = true;

	/**
	 * Is variable setup.
	 *
	 * @var bool
	 */
	private $is_setup = false;

	/**
	 * Hold arguments.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * Hold arguments temporarily.
	 *
	 * @var array
	 */
	protected $tmp_args = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$action   = is_admin() ? 'admin_enqueue_scripts' : 'wp';
		$priority = is_admin() ? 5 : 25;
		$this->action( $action, 'setup', $priority );
	}

	/**
	 * Should we setup variables or not.
	 *
	 * @return bool
	 */
	private function should_we_setup() {
		if ( Helper::is_ux_builder() ) {
			return false;
		}

		global $wp_customize;
		if ( isset( $wp_customize ) || $this->is_setup ) {
			return false;
		}

		$current_screen = \function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if (
			$current_screen instanceof \WP_Screen &&
			\in_array( $current_screen->base, [ 'themes' ], true )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Set up replacement variables.
	 */
	public function setup() {
		if ( ! $this->should_we_setup() ) {
			return;
		}

		// Variable setuped.
		$this->is_setup = true;

		// Internal variables.
		$current_screen = \function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( $current_screen instanceof \WP_Screen ) {
			$screen_base        = $current_screen->base;
			$this->is_post_edit = is_admin() && 'post' === $screen_base;
			$this->is_term_edit = is_admin() && 'term' === $screen_base;
			$this->is_user_edit = is_admin() && ( 'profile' === $screen_base || 'user-edit' === $screen_base );
		}

		/**
		 * Filter: strip variables which don't have a replacement.
		 *
		 * @param bool $final
		 */
		$this->remove_non_replaced = $this->do_filter( 'vars/remove_nonreplaced', true );

		// Setup internal variables.
		$this->setup_basic_variables();
		$this->setup_post_variables();
		$this->setup_term_variables();
		$this->setup_author_variables();
		$this->setup_advanced_variables();

		// Setup custom fields.
		if ( $this->is_post_edit || $this->is_term_edit || $this->is_user_edit ) {
			Helper::add_json( 'customFields', $this->get_custom_fields() );
			Helper::add_json( 'customTerms', $this->get_custom_taxonomies() );
		}

		/**
		* Action: 'rank_math/vars/register_extra_replacements' - Allows adding extra variables.
		*/
		$this->do_action( 'vars/register_extra_replacements' );
	}

	/**
	 * Setup JSON for use in ui.
	 */
	public function setup_json() {
		$json = [];

		foreach ( $this->replacements as $id => $variable ) {
			$json[ $id ] = $variable->to_array();
		}

		Helper::add_json( 'variables', $this->do_filter( 'vars/replacements', $json ) );
	}

	/**
	 * Get replacements.
	 *
	 * @return array
	 */
	public function get_replacements() {
		return $this->replacements;
	}

	/**
	 * Set arguments.
	 *
	 * @param array $args The object some of the replacement values might come from,
	 *                    could be a post, taxonomy or term.
	 */
	public function set_arguments( $args = [] ) {
		if ( ! empty( $args ) ) {
			$this->tmp_args = $this->args;
			$this->args     = $args;
		}
	}

	/**
	 * Reset arguments.
	 */
	public function reset_arguments() {
		$this->args = $this->tmp_args;
	}

	/**
	 * Get custom fields.
	 *
	 * @return array
	 */
	private function get_custom_fields() {
		$metas = [];

		if ( $this->is_user_edit ) {
			global $user_id;
			$metas = get_metadata( 'user', $user_id );
		} elseif ( $this->is_post_edit ) {
			$metas = get_metadata( 'post', $this->args->ID );
		} elseif ( $this->is_term_edit ) {
			$term_id = Param::request( 'tag_ID', 0, FILTER_VALIDATE_INT );
			$metas   = get_metadata( 'term', $term_id );
		}

		if ( empty( $metas ) ) {
			return [];
		}

		$json = [];
		foreach ( $metas as $meta_key => $meta_value ) {
			if ( Str::starts_with( '_', $meta_key ) || Str::starts_with( 'rank_math_', $meta_key ) ) {
				continue;
			}

			$json[ $meta_key ] = $meta_value[0];
		}

		return $json;
	}

	/**
	 * Get custom taxonomies.
	 *
	 * @return array
	 */
	private function get_custom_taxonomies() {
		$taxonomies = get_post_taxonomies( $this->args->ID );
		if ( empty( $taxonomies ) ) {
			return [];
		}

		$json = [];
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy, [ 'category', 'post_tag' ], true ) ) {
				continue;
			}

			$name = ucwords( str_replace( [ '_', '-' ], ' ', $taxonomy ) );
			/* translators: Taxonomy name. */
			$title = sprintf( __( '%s Title', 'rank-math' ), $name );
			/* translators: Taxonomy name. */
			$desc = sprintf( __( '%s Description', 'rank-math' ), $name );

			$this->register_replacement(
				"term_{$taxonomy}",
				[
					'name'        => $title,
					'description' => esc_html__( 'Custom Term title.', 'rank-math' ),
					'variable'    => "customterm({$taxonomy})",
					'example'     => $title,
				],
				[ $this, 'get_custom_term' ]
			);

			$this->register_replacement(
				"term_{$taxonomy}_desc",
				[
					'name'        => $desc,
					'description' => esc_html__( 'Custom Term description.', 'rank-math' ),
					'variable'    => "customterm_desc({$taxonomy})",
					'example'     => $desc,
				],
				[ $this, 'get_custom_term_desc' ]
			);

			$term      = $this->get_custom_term( $taxonomy );
			$term_desc = $this->get_custom_term_desc( $taxonomy );

			$json[ $taxonomy ]          = $term ? $term : $title;
			$json[ "{$taxonomy}_desc" ] = $term_desc ? $term_desc : $desc;
		}

		return $json;
	}
}
