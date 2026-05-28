<?php
/**
 * Fix runner service for the rank-math/fix-site-seo ability.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Applies automatic fixes for failing SEO analysis tests.
 */
class Fix_Runner {

	/**
	 * Maximum posts to process in a single synchronous fix call.
	 */
	const POST_LIMIT = 100;

	/**
	 * Apply the fix for a given test and return a result summary.
	 *
	 * @param string $test_id    SEO analysis test identifier.
	 * @param array  $input      Raw ability input (may include 'value', 'post_limit').
	 * @return array { fixed: bool, summary: string, details: array }
	 */
	public function fix( $test_id, array $input = [] ) {
		$handler = 'fix_' . $test_id;
		if ( ! method_exists( $this, $handler ) ) {
			return $this->cannot_fix(
				sprintf(
					/* translators: %s: test ID */
					__( 'No automatic fix is available for test "%s". Use the fix_text guidance to resolve it manually.', 'seo-by-rank-math' ),
					$test_id
				)
			);
		}

		return $this->{$handler}( $input );
	}

	/**
	 * Fix: make the site visible to search engines.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_blog_public( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		update_option( 'blog_public', 1 );
		return $this->ok( __( 'Site visibility set to public. Search engines can now index your site.', 'seo-by-rank-math' ) );
	}

	/**
	 * Fix: set permalink structure to /%postname%/.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_permalink_structure( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		global $wp_rewrite;

		if ( '/%postname%/' === get_option( 'permalink_structure' ) ) {
			return $this->ok( __( 'Permalink structure is already set to /%postname%/.', 'seo-by-rank-math' ) );
		}

		update_option( 'permalink_structure', '/%postname%/' );
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$wp_rewrite->flush_rules( true );

		return $this->ok( __( 'Permalink structure set to /%postname%/ and rewrite rules flushed.', 'seo-by-rank-math' ) );
	}

	/**
	 * Fix: set the site tagline to a custom value.
	 *
	 * Requires `value` in input — refuses if missing or looks like the default.
	 *
	 * @param array $input Must contain 'value'.
	 * @return array
	 */
	protected function fix_site_description( array $input ) {
		$value = isset( $input['value'] ) ? sanitize_text_field( $input['value'] ) : '';

		if ( empty( $value ) ) {
			return $this->cannot_fix(
				__( 'A "value" input is required to set the site tagline. Provide a short, unique description of your site.', 'seo-by-rank-math' )
			);
		}

		if ( 'just another wordpress site' === strtolower( $value ) ) { // phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText
			return $this->cannot_fix(
				__( 'The provided tagline is the default WordPress value. Please supply a unique tagline.', 'seo-by-rank-math' )
			);
		}

		update_option( 'blogdescription', $value );
		return $this->ok(
			sprintf(
				/* translators: %s: tagline value */
				__( 'Site tagline updated to: "%s".', 'seo-by-rank-math' ),
				$value
			)
		);
	}

	// -------------------------------------------------------------------------
	// Rank Math module fixes
	// -------------------------------------------------------------------------

	/**
	 * Fix: enable the Rank Math sitemap module.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_sitemaps( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		Helper::update_modules( [ 'sitemap' => 'on' ] );
		return $this->ok( __( 'Sitemap module enabled. Your XML sitemap is now active.', 'seo-by-rank-math' ) );
	}

	/**
	 * Fix: enable the Rank Math schema (rich snippet) module.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_schema( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		Helper::update_modules( [ 'rich-snippet' => 'on' ] );
		return $this->ok( __( 'Schema / rich snippet module enabled.', 'seo-by-rank-math' ) );
	}

	// -------------------------------------------------------------------------
	// Rank Math settings fixes
	// -------------------------------------------------------------------------

	/**
	 * Fix: ensure the global robots setting does not include noindex.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_noindex( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$titles = (array) get_option( 'rank-math-options-titles', [] );

		$robots = isset( $titles['robots_global'] ) ? (array) $titles['robots_global'] : [ 'index', 'follow' ];
		$robots = array_diff( $robots, [ 'noindex' ] );
		if ( ! in_array( 'index', $robots, true ) ) {
			$robots[] = 'index';
		}
		$titles['robots_global'] = array_values( $robots );

		update_option( 'rank-math-options-titles', $titles );
		return $this->ok( __( 'Global robots setting updated to allow indexing. Run a fresh audit to confirm the remote API test passes.', 'seo-by-rank-math' ) );
	}

	/**
	 * Fix: OpenGraph is always enabled in Rank Math — explain to the AI.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_opengraph( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return $this->cannot_fix(
			__( 'OpenGraph output is always enabled in Rank Math. If this test is failing, the remote audit API may need a fallback Open Graph image. Set one at Rank Math → Titles & Meta → Global Meta → Open Graph Thumbnail.', 'seo-by-rank-math' )
		);
	}

	/**
	 * Fix: write a safe default robots.txt content into Rank Math settings.
	 *
	 * @param array $input Unused.
	 * @return array
	 */
	protected function fix_robots_txt( array $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$general = (array) get_option( 'rank-math-options-general', [] );

		if ( isset( $general['robots_txt_content'] ) && '' !== trim( $general['robots_txt_content'] ) ) {
			return $this->ok( __( 'Robots.txt content is already configured in Rank Math settings.', 'seo-by-rank-math' ) );
		}

		$general['robots_txt_content'] = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php";
		update_option( 'rank-math-options-general', $general );

		return $this->ok( __( 'Default robots.txt content written to Rank Math settings.', 'seo-by-rank-math' ) );
	}

	// -------------------------------------------------------------------------
	// Per-post bulk fixes
	// -------------------------------------------------------------------------

	/**
	 * Fix: set a focus keyword on every published post that is missing one.
	 *
	 * The keyword is derived from the first 3 significant words of the post title.
	 * Accepts optional 'post_limit' input (default: POST_LIMIT).
	 *
	 * @param array $input Optional: post_limit (int).
	 * @return array
	 */
	protected function fix_focus_keywords( array $input ) {
		$limit = isset( $input['post_limit'] ) ? absint( $input['post_limit'] ) : self::POST_LIMIT;
		$limit = max( 1, min( $limit, 500 ) );
		$types = Helper::get_allowed_post_types();

		if ( empty( $types ) ) {
			return $this->cannot_fix( __( 'No indexable post types are configured in Rank Math.', 'seo-by-rank-math' ) );
		}

		$posts = get_posts(
			[
				'post_type'      => $types,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery
					[
						'key'     => 'rank_math_focus_keyword',
						'compare' => 'NOT EXISTS',
					],
				],
				'fields'         => 'ids',
			]
		);

		if ( empty( $posts ) ) {
			return $this->ok( __( 'All published posts already have a focus keyword set.', 'seo-by-rank-math' ) );
		}

		$updated   = 0;
		$truncated = count( $posts ) >= $limit;

		foreach ( $posts as $post_id ) {
			$title   = get_the_title( $post_id );
			$keyword = $this->keyword_from_title( $title );
			if ( empty( $keyword ) ) {
				continue;
			}
			update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );
			++$updated;
		}

		$summary = sprintf(
			/* translators: 1: count updated, 2: total found */
			__( 'Set focus keywords on %1$d posts (derived from post titles).', 'seo-by-rank-math' ),
			$updated
		);

		if ( $truncated ) {
			$summary .= ' ' . sprintf(
				/* translators: %d: post_limit */
				__( 'Results were limited to %d posts — run again to continue.', 'seo-by-rank-math' ),
				$limit
			);
		}

		return $this->ok(
			$summary,
			[
				'updated'   => $updated,
				'truncated' => $truncated,
			]
		);
	}

	/**
	 * Fix: update the focus keyword to match the existing post title for posts where it doesn't.
	 *
	 * Changing the focus keyword (not the title) is safe: it doesn't affect URLs or published content.
	 * Accepts optional 'post_limit' input (default: POST_LIMIT).
	 *
	 * @param array $input Optional: post_limit (int).
	 * @return array
	 */
	protected function fix_post_titles( array $input ) {
		global $wpdb;

		$limit = isset( $input['post_limit'] ) ? absint( $input['post_limit'] ) : self::POST_LIMIT;
		$limit = max( 1, min( $limit, 500 ) );
		$types = Helper::get_allowed_post_types();

		if ( empty( $types ) ) {
			return $this->cannot_fix( __( 'No indexable post types are configured in Rank Math.', 'seo-by-rank-math' ) );
		}

		$in_post_types = "'" . implode( "','", array_map( 'esc_sql', $types ) ) . "'";

		// Reuse the same query as the test — posts with focus keyword set but keyword not in title.
		$meta_query = new \WP_Meta_Query(
			[
				'relation' => 'AND',
				[
					'key'     => 'rank_math_focus_keyword',
					'compare' => 'EXISTS',
				],
				[
					'relation' => 'OR',
					[
						'key'     => 'rank_math_robots',
						'value'   => 'noindex',
						'compare' => 'NOT LIKE',
					],
					[
						'key'     => 'rank_math_robots',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		$mq_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				"SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_title
				FROM {$wpdb->posts} {$mq_sql['join']}
				WHERE 1=1 {$mq_sql['where']}
				AND {$wpdb->posts}.post_type IN ({$in_post_types})
				AND {$wpdb->posts}.post_status = 'publish'
				AND REPLACE( {$wpdb->posts}.post_title, %s, %s ) NOT LIKE
					CONCAT( %s, SUBSTRING_INDEX( {$wpdb->postmeta}.meta_value, %s, 1 ), %s )
				GROUP BY {$wpdb->posts}.ID
				LIMIT %d",
				'&amp;',
				'&',
				'%',
				',',
				'%',
				$limit
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		if ( empty( $rows ) ) {
			return $this->ok( __( 'All posts with focus keywords already include them in their titles.', 'seo-by-rank-math' ) );
		}

		$updated   = 0;
		$truncated = count( $rows ) >= $limit;

		foreach ( $rows as $row ) {
			$keyword = $this->keyword_from_title( $row->post_title );
			if ( empty( $keyword ) ) {
				continue;
			}
			update_post_meta( (int) $row->ID, 'rank_math_focus_keyword', $keyword );
			++$updated;
		}

		$summary = sprintf(
			/* translators: %d: count updated */
			__( 'Updated focus keywords on %d posts to match their titles.', 'seo-by-rank-math' ),
			$updated
		);

		if ( $truncated ) {
			$summary .= ' ' . sprintf(
				/* translators: %d: post_limit */
				__( 'Results were limited to %d posts — run again to continue.', 'seo-by-rank-math' ),
				$limit
			);
		}

		return $this->ok(
			$summary,
			[
				'updated'   => $updated,
				'truncated' => $truncated,
			]
		);
	}

	/**
	 * Derive a focus keyword from a post title.
	 *
	 * Returns the first 3 words, lower-cased, with punctuation stripped.
	 * Returns empty string if nothing usable remains.
	 *
	 * @param string $title Post title.
	 * @return string
	 */
	private function keyword_from_title( $title ) {
		$words = preg_split( '/\s+/', strtolower( html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		$words = array_values(
			array_filter(
				$words,
				function ( $w ) {
					return ! empty( preg_replace( '/[^a-z0-9]/i', '', $w ) );
				}
			)
		);

		return implode( ' ', array_slice( $words, 0, 3 ) );
	}

	/**
	 * Return a successful fix result.
	 *
	 * @param string $summary Human-readable summary.
	 * @param array  $details Optional extra data.
	 * @return array
	 */
	private function ok( $summary, array $details = [] ) {
		return [
			'fixed'   => true,
			'summary' => $summary,
			'details' => $details,
		];
	}

	/**
	 * Return a result indicating the fix could not be applied.
	 *
	 * @param string $summary Reason why the fix could not be applied.
	 * @return array
	 */
	private function cannot_fix( $summary ) {
		return [
			'fixed'   => false,
			'summary' => $summary,
			'details' => [],
		];
	}
}
