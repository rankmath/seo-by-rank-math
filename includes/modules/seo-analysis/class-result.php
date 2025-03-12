<?php
/**
 * The SEO Analyzer result of each test.
 *
 * @since      1.0.24
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

/**
 * Result class.
 */
class Result {

	/**
	 * Result ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Hold result data.
	 *
	 * @var array
	 */
	private $result;

	/**
	 * Is sub-page.
	 *
	 * @var array
	 */
	private $is_subpage;

	/**
	 * The Constructor.
	 *
	 * @param string $id         Result id.
	 * @param object $data       Result data.
	 * @param bool   $is_subpage Is sub-page result.
	 */
	public function __construct( $id, $data, $is_subpage ) {
		if ( is_a( $data, 'RankMath\\SEO_Analysis\\Result' ) ) {
			$data = $data->result;
		}
		$this->id         = $id;
		$this->result     = $data;
		$this->is_subpage = $is_subpage;
	}

	/**
	 * Get result ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get result category.
	 *
	 * @return string
	 */
	public function get_category() {
		return is_array( $this->result ) && isset( $this->result['category'] ) ? $this->result['category'] : '';
	}

	/**
	 * Get result status.
	 *
	 * @return string
	 */
	public function get_status() {
		return is_array( $this->result ) && isset( $this->result['status'] ) ? $this->result['status'] : '';
	}

	/**
	 * Is test excluded.
	 *
	 * @return bool
	 */
	public function is_excluded() {
		$exclude_tests = [
			'active_plugins',
			'active_theme',
			'dirlist',
			'libwww_perl_access',
			'robots_txt',
			'safe_browsing',
			'xmlrpc',

			// Local tests.
			'comment_pagination',
			'site_description',
			'permalink_structure',
			'cache_plugin',
			'search_console',
			'focus_keywords',
			'post_titles',
		];

		return $this->is_subpage && in_array( $this->id, $exclude_tests, true );
	}

	/**
	 * Is test hidden.
	 *
	 * @return bool
	 */
	public function is_hidden() {
		$always_hidden = [
			'serp_preview',
			'mobile_serp_preview',
		];

		// Hidden when not in advanced mode.
		$hidden_tests = [
			// Performance.
			'image_header',
			'minify_css',
			'minify_js',
			'page_objects',
			'page_size',
			'response_time',

			// Security.
			'directory_listing',
			'safe_browsing',
			'ssl',
			'active_plugins',
			'active_theme',
		];

		$is_hidden = in_array( $this->id, $always_hidden, true ) || ( ! Helper::is_advanced_mode() && in_array( $this->id, $hidden_tests, true ) );

		return apply_filters( 'rank_math/seo_analysis/is_test_hidden', $is_hidden, $this->id );
	}

	/**
	 * Get test score.
	 *
	 * @return int
	 */
	public function get_score() {
		$score = [
			'h1_heading'          => 5,
			'h2_headings'         => 2,
			'img_alt'             => 4,
			'keywords_meta'       => 5,
			'links_ratio'         => 3,
			'title_length'        => 4,
			'permalink_structure' => 7,
			'focus_keywords'      => 3,
			'post_titles'         => 4,

			// Advanced SEO.
			'canonical'           => 5,
			'noindex'             => 7,
			'non_www'             => 4,
			'opengraph'           => 2,
			'robots_txt'          => 3,
			'schema'              => 3,
			'sitemaps'            => 3,
			'search_console'      => 1,

			// Performance.
			'image_header'        => 3,
			'minify_css'          => 2,
			'minify_js'           => 1,
			'page_objects'        => 2,
			'page_size'           => 3,
			'response_time'       => 3,

			// Security.
			'directory_listing'   => 1,
			'safe_browsing'       => 8,
			'ssl'                 => 7,
		];

		return isset( $score[ $this->id ] ) ? $score[ $this->id ] : 0;
	}

	/**
	 * Get test result data.
	 *
	 * @return array
	 */
	public function get_result() {
		return $this->result;
	}
}
