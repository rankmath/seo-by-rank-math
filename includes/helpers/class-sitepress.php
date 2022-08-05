<?php
/**
 * The Sitepress helpers.
 *
 * @since      1.0.40
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Sitepress class.
 */
class Sitepress {

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_get_term = false;

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_terms_clauses = false;

	/**
	 * Has filter removed.
	 *
	 * @var boolean
	 */
	private $has_get_terms_args = false;

	/**
	 * Has home_url filter removed.
	 *
	 * @var boolean
	 */
	private $has_home_url = false;


	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Sitepress
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Sitepress ) ) {
			$instance = new Sitepress();
		}

		return $instance;
	}

	/**
	 * Remove term filters.
	 */
	public function remove_term_filters() {
		if ( ! $this->is_active() ) {
			return;
		}

		$sitepress = $this->get_var();

		$this->has_get_category   = remove_filter( 'category_link', [ $sitepress, 'category_link_adjust_id' ], 1 );
		$this->has_get_term       = remove_filter( 'get_term', [ $sitepress, 'get_term_adjust_id' ], 1 );
		$this->has_terms_clauses  = remove_filter( 'terms_clauses', [ $sitepress, 'terms_clauses' ] );
		$this->has_get_terms_args = remove_filter( 'get_terms_args', [ $sitepress, 'get_terms_args_filter' ] );
	}

	/**
	 * Restore term filters.
	 */
	public function restore_term_filters() {
		if ( ! $this->is_active() ) {
			return;
		}

		$sitepress = $this->get_var();

		if ( $this->has_get_category ) {
			$this->has_get_category = false;
			add_filter( 'category_link', [ $sitepress, 'category_link_adjust_id' ], 1, 1 );
		}

		if ( $this->has_get_term ) {
			$this->has_get_term = false;
			add_filter( 'get_term', [ $sitepress, 'get_term_adjust_id' ], 1, 1 );
		}

		if ( $this->has_terms_clauses ) {
			$this->has_terms_clauses = false;
			add_filter( 'terms_clauses', [ $sitepress, 'terms_clauses' ], 10, 3 );
		}

		if ( $this->has_get_terms_args ) {
			$this->has_get_terms_args = false;
			add_filter( 'get_terms_args', [ $sitepress, 'get_terms_args_filter' ], 10, 2 );
		}
	}

	/**
	 * Remove home_url filter.
	 */
	public function remove_home_url_filter() {
		if ( ! $this->is_active() ) {
			return;
		}

		global $wpml_url_filters;
		$this->has_home_url = remove_filter( 'home_url', [ $wpml_url_filters, 'home_url_filter' ], -10 );
	}

	/**
	 * Restore home_url filter.
	 */
	public function restore_home_url_filter() {
		if ( ! $this->is_active() ) {
			return;
		}

		if ( $this->has_home_url ) {
			global $wpml_url_filters;
			$this->has_home_url = false;
			add_filter( 'home_url', [ $wpml_url_filters, 'home_url_filter' ], -10, 4 );
		}
	}

	/**
	 * Is plugin active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return isset( $GLOBALS['sitepress'] );
	}

	/**
	 * Get sitepress global variable.
	 *
	 * @return object
	 */
	public function get_var() {
		return $GLOBALS['sitepress'];
	}

	/**
	 * Delete cached tax permalink.
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The taxonomy name.
	 * @return void
	 */
	public function delete_cached_tax_permalink( $term_id, $taxonomy ) {
		if ( ! $this->is_active() ) {
			return;
		}

		wp_cache_delete(
			md5( wp_json_encode( [ $term_id, $taxonomy, false ] ) ),
			'icl_tax_permalink_filter'
		);
	}
}
