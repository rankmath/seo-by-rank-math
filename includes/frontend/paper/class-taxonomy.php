<?php
/**
 * The Term Class
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\Term;
use RankMath\Helper;
use WP_Term;
defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy class.
 */
class Taxonomy implements IPaper {

	/**
	 * Term object.
	 *
	 * @var WP_Term
	 */
	private $object;

	/**
	 * Retrieve Term instance.
	 *
	 * @param  WP_Term|object|int $term_id Term to get using (int) term_id.
	 * @return Term|false Term object, false otherwise.
	 */
	public static function get( $term_id = 0 ) {
		return false !== Term::get( $term_id ) ? WP_Term::get_instance( $term_id ) : null;
	}

	/**
	 * Set term object.
	 *
	 * @param WP_Term $object Current term object.
	 */
	public function set_object( $object = null ) {
		return ! is_null( $object ) ? $object : get_queried_object();
	}

	/**
	 * Retrieves the SEO title for a taxonomy.
	 *
	 * @return string The SEO title for the taxonomy.
	 */
	public function title() {
		$object = $this->set_object();
		if ( ! is_object( $object ) ) {
			return Paper::get_from_options( '404_title', [], esc_html__( 'Page not found', 'rank-math' ) );
		}

		$title = Term::get_meta( 'title', $object, $object->taxonomy );
		if ( '' !== $title ) {
			return $title;
		}

		return Paper::get_from_options( "tax_{$object->taxonomy}_title", $object );
	}

	/**
	 * Retrieves the SEO description for a taxonomy.
	 *
	 * @return string The SEO description for the taxonomy.
	 */
	public function description() {
		$object      = $this->set_object();
		$description = Term::get_meta( 'description', $object, $object->taxonomy );
		if ( '' !== $description ) {
			return $description;
		}

		return Paper::get_from_options( "tax_{$object->taxonomy}_description", $object );
	}

	/**
	 * Retrieves the robots for a taxonomy.
	 *
	 * @return string The robots for the taxonomy
	 */
	public function robots() {
		$object = $this->set_object();
		$robots = Paper::robots_combine( Term::get_meta( 'robots', $object ) );

		if ( is_object( $object ) && empty( $robots ) && Helper::get_settings( "titles.tax_{$object->taxonomy}_custom_robots" ) ) {
			$robots = Paper::robots_combine( Helper::get_settings( "titles.tax_{$object->taxonomy}_robots" ), true );
		}

		if ( $this->noindex_term( $object ) ) {
			$robots['index'] = 'noindex';
		}

		return $robots;
	}

	/**
	 * Retrieves the advanced robots for a taxonomy.
	 *
	 * @return array The advanced robots for the taxonomy
	 */
	public function advanced_robots() {
		$object = $this->set_object();
		$robots = Paper::advanced_robots_combine( Term::get_meta( 'advanced_robots', $object ) );

		if ( is_object( $object ) && empty( $robots ) && Helper::get_settings( "titles.tax_{$object->taxonomy}_custom_robots" ) ) {
			$robots = Paper::advanced_robots_combine( Helper::get_settings( "titles.tax_{$object->taxonomy}_advanced_robots" ), true );
		}

		return $robots;
	}

	/**
	 * Retrieves the canonical URL.
	 *
	 * @return array
	 */
	public function canonical() {
		$object = $this->set_object();

		if ( empty( $object ) || Term::is_multiple_terms_query() ) {
			return [];
		}

		$term_link = get_term_link( $object, $object->taxonomy );

		return [
			'canonical'          => is_wp_error( $term_link ) ? '' : $term_link,
			'canonical_override' => Term::get_meta( 'canonical_url', $object, $object->taxonomy ),
		];
	}

	/**
	 * Retrieves the keywords.
	 *
	 * @return string The focus keywords.
	 */
	public function keywords() {
		$object = $this->set_object();

		if ( empty( $object ) || Term::is_multiple_terms_query() ) {
			return '';
		}

		return Term::get_meta( 'focus_keyword', $object, $object->taxonomy );
	}

	/**
	 * Whether to noindex empty terms.
	 *
	 * @param object $object Current taxonomy term object.
	 *
	 * @return bool
	 */
	private function noindex_term( $object ) {
		if ( Term::is_multiple_terms_query() ) {
			return true;
		}

		if ( is_object( $object ) && 0 === $object->count && Helper::get_settings( 'titles.noindex_empty_taxonomies' ) ) {
			$children = get_terms(
				$object->taxonomy,
				[
					'parent' => $object->term_id,
					'number' => 1,
					'fields' => 'ids',
				]
			);

			if ( empty( $children ) ) {
				return true;
			}
		}

		return false;
	}
}
