<?php
/**
 * The HTML sitemap generator for authors.
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap\Html;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Terms class.
 */
class Authors {

	use Hooker;

	/**
	 * Get all authors.
	 *
	 * @return array
	 */
	private function get_authors() {
		$sort_map = [
			'published' => [
				'field' => 'user_registered',
				'order' => 'DESC',
			],
			'modified'  => [
				'field' => 'user_registered',
				'order' => 'DESC',
			],
			'alphabetical' => [
				'field' => 'display_name',
				'order' => 'ASC',
			],
			'post_id' => [
				'field' => 'ID',
				'order' => 'DESC',
			],
		];

		$sort_setting = Helper::get_settings( 'sitemap.html_sitemap_sort' );
		$sort = ( isset( $sort_map[ $sort_setting ] ) ) ? $sort_map[ $sort_setting ] : $sort_map['published'];

		/**
		 * Filter: 'rank_math/sitemap/html_sitemap/sort_items' - Allow changing the sort order of the HTML sitemap.
		 *
		 * @var array $sort {
		 *    @type string $field The field to sort by.
		 *    @type string $order The sort order.
		 * }
		 * @var string $order The item type.
		 * @var string $empty Empty string (unused).
		 */
		$sort = $this->do_filter( 'sitemap/html_sitemap/sort_items', $sort, 'authors', '' );

		$exclude = wp_parse_id_list( Helper::get_settings( 'sitemap.exclude_users' ) );
		$table   = Database::table( 'users' );

		$query = $table->select( [ 'ID', 'display_name', 'user_nicename' ] );

		if ( ! empty( $exclude ) ) {
			$query->whereNotIn( 'ID', $exclude );
		}

		$authors = $query->orderBy( $sort['field'], $sort['order'] )->get();

		return array_filter( $authors, [ $this, 'should_include_user' ] );
	}

	/**
	 * Check if user is not in the excluded roles and doesn't have noindex set.
	 *
	 * @param object $user Partial user data from database.
	 *
	 * @return bool
	 */
	private function should_include_user( $user ) {
		$excluded_roles = (array) Helper::get_settings( 'sitemap.exclude_roles' );
		$roles          = (array) get_userdata( $user->ID )->roles;
		$intersect      = array_intersect( $roles, $excluded_roles );
		if ( ! empty( $intersect ) ) {
			return false;
		}

		$robots = get_user_meta( $user->ID, 'rank_math_robots', true );
		if ( is_array( $robots ) && in_array( 'noindex', $robots, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate the HTML sitemap for authors.
	 *
	 * @return string
	 */
	public function generate_sitemap() {
		$users = $this->get_authors();
		if ( empty( $users ) ) {
			return '';
		}

		$output[] = '<div class="rank-math-html-sitemap__section rank-math-html-sitemap__section--authors">';
		$output[] = '<h2 class="rank-math-html-sitemap__title">' . esc_html__( 'Authors', 'rank-math' ) . '</h2>';
		$output[] = '<ul class="rank-math-html-sitemap__list">';
		$output[] = $this->generate_authors_list( $users );
		$output[] = '</ul>';
		$output[] = '</div>';

		$output = implode( '', $output );

		return $output;
	}

	/**
	 * Generate HTML list of authors.
	 *
	 * @param array $authors List of authors.
	 *
	 * @return string
	 */
	private function generate_authors_list( $authors ) {
		if ( empty( $authors ) ) {
			return '';
		}

		$output = [];
		foreach ( $authors as $author ) {
			$output[] = '<li class="rank-math-html-sitemap__item">'
				. '<a href="' . esc_url( $this->get_author_link( $author ) ) . '" class="rank-math-html-sitemap__link">'
				. esc_html( $this->get_author_title( $author ) )
				. '</a>'
				. '</li>';
		}

		return implode( '', $output );
	}

	/**
	 * Get the author link.
	 *
	 * @param object $author Author data from database (not a WP_User object).
	 *
	 * @return string
	 */
	private function get_author_link( $author ) {
		return get_author_posts_url( $author->ID, $author->user_nicename );
	}

	/**
	 * Get the author title.
	 *
	 * @param object $author The author data.
	 *
	 * @return string
	 */
	private function get_author_title( $author ) {
		if ( Helper::get_settings( 'sitemap.html_sitemap_seo_titles' ) !== 'seo_titles' ) {
			return $author->display_name;
		}

		// Custom SEO title.
		$meta = get_user_meta( $author->ID, 'rank_math_title', true );
		if ( ! empty( $meta ) ) {
			return Helper::replace_vars( $meta, $author );
		}

		// Default SEO title from the global settings.
		$template = Helper::get_settings( 'titles.pt_author_title' );
		if ( ! empty( $template ) ) {
			return Helper::replace_vars( $template, $author );
		}

		// Fallback to author name.
		return $author->display_name;
	}
}
