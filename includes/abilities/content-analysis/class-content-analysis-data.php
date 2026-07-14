<?php
/**
 * Gathers raw content facts for the rank-math/analyze-post-content ability.
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Content_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Content_Analysis;

use WP_Post;
use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Helpers\Url;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the raw content payload an AI agent needs to evaluate the on-page SEO tests itself.
 *
 * This class does NOT score or judge anything — the actual test logic (Analyzer/Paper/Researcher)
 * only exists client-side in JS and has no PHP port. Instead, it assembles the same facts the JS
 * engine reads off the `Paper` object, so the calling agent can run the per-test evaluation.
 */
class Content_Analysis_Data {

	use Hooker;

	/**
	 * Canonical on-page test IDs for a post, mirroring Post_Screen::get_analysis().
	 *
	 * @var array
	 */
	const DEFAULT_TESTS = [
		'contentHasTOC'             => true,
		'contentHasShortParagraphs' => true,
		'contentHasAssets'          => true,
		'keywordInTitle'            => true,
		'keywordInMetaDescription'  => true,
		'keywordInPermalink'        => true,
		'keywordIn10Percent'        => true,
		'keywordInContent'          => true,
		'keywordInSubheadings'      => true,
		'keywordInImageAlt'         => true,
		'keywordDensity'            => true,
		'keywordNotUsed'            => true,
		'lengthContent'             => true,
		'lengthPermalink'           => true,
		'linksHasInternal'          => true,
		'linksHasExternals'         => true,
		'linksNotAllExternals'      => true,
		'titleStartWithKeyword'     => true,
		'titleSentiment'            => true,
		'titleHasPowerWords'        => true,
		'titleHasNumber'            => true,
		'hasContentAI'              => true,
	];

	/**
	 * Build the content analysis payload for a post.
	 *
	 * @param WP_Post $post    Post to analyze.
	 * @param string  $keyword Optional focus keyword override.
	 * @return array
	 */
	public function get( WP_Post $post, string $keyword = '' ): array {
		$post_id     = $post->ID;
		$body        = $this->get_plain_text( $post->post_content );
		$first_tenth = (int) ceil( mb_strlen( $body ) * 0.1 );
		$title       = Helper::get_post_meta( 'title', $post_id );
		$description = Helper::get_post_meta( 'description', $post_id );

		return [
			'tests_to_run' => array_keys( $this->get_tests( $post->post_type ) ),
			'content'      => [
				'title'         => ! empty( $title ) ? $title : get_the_title( $post_id ),
				'description'   => ! empty( $description ) ? $description : '',
				'permalink'     => str_replace( home_url(), '', (string) get_permalink( $post_id ) ),
				'focus_keyword' => '' !== $keyword ? $keyword : $this->get_primary_keyword( $post_id ),
				'body'          => $body,
				'word_count'    => str_word_count( $body ),
				'excerpt_10pct' => Str::substr( $body, 0, $first_tenth ),
				'headings'      => $this->get_headings( $post->post_content ),
				'images'        => $this->get_images( $post->post_content ),
				'links'         => $this->get_links( $post->post_content ),
			],
		];
	}

	/**
	 * Get the on-page test IDs registered for a post type.
	 *
	 * @param string $post_type Post type slug.
	 * @return array
	 */
	private function get_tests( string $post_type ): array {
		return $this->do_filter( 'researches/tests', self::DEFAULT_TESTS, $post_type );
	}

	/**
	 * Strip a post's content down to plain text.
	 *
	 * @param string $content Raw post content (may contain blocks/shortcodes/HTML).
	 * @return string
	 */
	private function get_plain_text( string $content ): string {
		$content = strip_shortcodes( $content );
		$content = wp_strip_all_tags( $content );

		return trim( preg_replace( '/\s+/', ' ', $content ) );
	}

	/**
	 * Extract heading text grouped by level (H1-H6).
	 *
	 * @param string $content Raw post content.
	 * @return array
	 */
	private function get_headings( string $content ): array {
		$headings = [];
		if ( ! preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER ) ) {
			return $headings;
		}

		foreach ( $matches as $match ) {
			$headings[] = [
				'level' => 'h' . $match[1],
				'text'  => trim( wp_strip_all_tags( $match[2] ) ),
			];
		}

		return $headings;
	}

	/**
	 * Extract image alt text from post content.
	 *
	 * @param string $content Raw post content.
	 * @return array
	 */
	private function get_images( string $content ): array {
		$images = [];
		if ( ! preg_match_all( '/<img[^>]+>/i', $content, $matches ) ) {
			return $images;
		}

		foreach ( $matches[0] as $tag ) {
			$alt      = preg_match( '/alt=["\']([^"\']*)["\']/i', $tag, $alt_match ) ? $alt_match[1] : '';
			$images[] = [ 'alt' => $alt ];
		}

		return $images;
	}

	/**
	 * Extract links from post content, classified as internal or external.
	 *
	 * @param string $content Raw post content.
	 * @return array
	 */
	private function get_links( string $content ): array {
		$links = [];
		if ( ! preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches ) ) {
			return $links;
		}

		foreach ( $matches[1] as $href ) {
			if ( Str::starts_with( '#', $href ) ) {
				continue;
			}

			$links[] = [
				'url'      => $href,
				'external' => Url::is_external( $href ),
			];
		}

		return $links;
	}

	/**
	 * Get the primary focus keyword for the post.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_primary_keyword( int $post_id ): string {
		$keywords = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		if ( empty( $keywords ) ) {
			return '';
		}

		$parts = explode( ',', $keywords );
		return trim( $parts[0] );
	}
}
