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
}
