<?php
/**
 * Term variable replacer.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Replace_Variables
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Replace_Variables;

use RankMath\Helpers\Str;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Term_Variables class.
 */
class Term_Variables extends Basic_Variables {

	/**
	 * Setup term variables.
	 */
	public function setup_term_variables() {
		if ( $this->is_term_edit ) {
			$tag_id     = Param::request( 'tag_ID', 0, FILTER_VALIDATE_INT );
			$term       = get_term( $tag_id, $GLOBALS['taxnow'], OBJECT );
			$this->args = $term;
		}

		$this->register_replacement(
			'term',
			[
				'name'        => esc_html__( 'Current Term', 'rank-math' ),
				'description' => esc_html__( 'Current term name', 'rank-math' ),
				'variable'    => 'term',
				'example'     => $this->is_term_edit ? $term->name : esc_html__( 'Example Term', 'rank-math' ),
				'nocache'     => true,
			],
			[ $this, 'get_term' ]
		);

		$this->register_replacement(
			'term_description',
			[
				'name'        => esc_html__( 'Term Description', 'rank-math' ),
				'description' => esc_html__( 'Current term description', 'rank-math' ),
				'variable'    => 'term_description',
				'example'     => $this->is_term_edit ? wp_strip_all_tags( term_description( $term ), true ) : esc_html__( 'Example Term Description', 'rank-math' ),
			],
			[ $this, 'get_term_description' ]
		);

		$this->register_replacement(
			'customterm',
			[
				'name'        => esc_html__( 'Custom Term (advanced)', 'rank-math' ),
				'description' => esc_html__( 'Custom term value.', 'rank-math' ),
				'variable'    => 'customterm(taxonomy-name)',
				'example'     => esc_html__( 'Custom term value', 'rank-math' ),
			],
			[ $this, 'get_custom_term' ]
		);

		$this->register_replacement(
			'customterm_desc',
			[
				'name'        => esc_html__( 'Custom Term description', 'rank-math' ),
				'description' => esc_html__( 'Custom Term description.', 'rank-math' ),
				'variable'    => 'customterm_desc(taxonomy-name)',
				'example'     => esc_html__( 'Custom Term description.', 'rank-math' ),
			],
			[ $this, 'get_custom_term_desc' ]
		);
	}

	/**
	 * Get the term name to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_term() {
		global $wp_query;

		if ( is_category() || is_tag() || is_tax() ) {
			return $wp_query->queried_object->name;
		}

		return ! empty( $this->args->taxonomy ) && ! empty( $this->args->name ) ? $this->args->name : null;
	}

	/**
	 * Get the term description to use as a replacement.
	 *
	 * @return string|null
	 */
	public function get_term_description() {
		global $wp_query;

		if ( is_category() || is_tag() || is_tax() ) {
			return $wp_query->queried_object->description;
		}

		if ( ! isset( $this->args->term_id ) || empty( $this->args->taxonomy ) ) {
			return null;
		}

		$term_desc = get_term_field( 'description', $this->args->term_id, $this->args->taxonomy );
		return '' !== $term_desc ? Str::truncate( $term_desc, 160 ) : null;
	}

	/**
	 * Get a custom taxonomy term to use as a replacement.
	 *
	 * @param string $taxonomy The name of the taxonomy.
	 *
	 * @return string|null
	 */
	public function get_custom_term( $taxonomy ) {
		return Str::is_non_empty( $taxonomy ) ? $this->get_terms( $this->args->ID, $taxonomy, true, [], 'name' ) : null;
	}

	/**
	 * Get a custom taxonomy term description to use as a replacement.
	 *
	 * @param string $taxonomy The name of the taxonomy.
	 *
	 * @return string|null
	 */
	public function get_custom_term_desc( $taxonomy ) {
		return Str::is_non_empty( $taxonomy ) ? $this->get_terms( $this->args->ID, $taxonomy, true, [], 'description' ) : null;
	}
}
