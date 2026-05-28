<?php
/**
 * Fix hint map for the rank-math/audit-site-seo ability.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

defined( 'ABSPATH' ) || exit;

/**
 * Maps SEO-audit test IDs to structured fix hints an agent can act on.
 *
 * Kind:
 *   - 'wp_admin'  → user/agent should open this WP admin page
 *   - 'setting'   → fix is a Rank Math setting (`rank-math-options-{section}`)
 *   - 'ability'   → another Rank Math ability can fix it (only entries for shipped abilities)
 *   - 'manual'    → content edit per post; no single setting flip will resolve it
 *
 * Tests not in this map return null for fix_hint; the agent falls back to the
 * `fix_html` text. Entries are added here as new abilities ship.
 */
class Fix_Hint_Map {

	/**
	 * Retrieves the fix hint for a given test ID.
	 *
	 * @param string $test_id The ID of the test for which to retrieve a fix hint.
	 * @return array|null The fix hint array or null if not found.
	 */
	public static function get( $test_id ) {
		$map = [
			// Single WP setting fix.
			'site_description'    => [
				'kind'   => 'wp_admin',
				'target' => 'options-general.php',
			],
			'permalink_structure' => [
				'kind'   => 'wp_admin',
				'target' => 'options-permalink.php',
			],
			'blog_public'         => [
				'kind'   => 'wp_admin',
				'target' => 'options-reading.php',
			],

			// Rank Math settings panels.
			'sitemaps'            => [
				'kind'   => 'setting',
				'target' => 'rank-math-options-sitemap',
			],
			'noindex'             => [
				'kind'   => 'setting',
				'target' => 'rank-math-options-titles',
			],
			'opengraph'           => [
				'kind'   => 'setting',
				'target' => 'rank-math-options-titles',
			],
			'schema'              => [
				'kind'   => 'setting',
				'target' => 'rank-math-options-titles',
			],
			'robots_txt'          => [
				'kind'   => 'setting',
				'target' => 'rank-math-options-general',
			],

			// Per-post content edits — no single flip; agent must walk posts.
			'h1_heading'          => [
				'kind'   => 'manual',
				'target' => 'edit_post_content',
			],
			'h2_headings'         => [
				'kind'   => 'manual',
				'target' => 'edit_post_content',
			],
			'keywords_meta'       => [
				'kind'   => 'manual',
				'target' => 'edit_post_keywords',
			],
			'title_length'        => [
				'kind'   => 'manual',
				'target' => 'edit_post_titles',
			],
		];

		return isset( $map[ $test_id ] ) ? $map[ $test_id ] : null;
	}
}
