<?php
/**
 * The Yoast TOC Block Converter.
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helpers\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast_TOC_Converter class.
 */
class Yoast_TOC_Converter {

	/**
	 * Convert TOC blocks to Rank Math.
	 *
	 * @param array $block Block to convert.
	 *
	 * @return array
	 */
	public function convert( $block ) {
		$exclude_headings = [];
		if ( ! empty( $block['attrs']['maxHeadingLevel'] ) ) {
			for ( $i = $block['attrs']['maxHeadingLevel'] + 1; $i <= 6; $i++ ) {
				$exclude_headings[] = "h{$i}";
			}
		}

		$new_block = [
			'blockName' => 'rank-math/toc-block',
			'attrs'     => [
				'title'           => $this->get_toc_title( $block['innerHTML'] ),
				'headings'        => $this->get_headings( $block['innerHTML'] ),
				'listStyle'       => 'ul',
				'titleWrapper'    => 'h2',
				'excludeHeadings' => $exclude_headings,
			],
		];

		$new_block['innerContent'][] = $this->get_html( $block['innerHTML'] );

		return $new_block;
	}

	/**
	 * Replace block in content.
	 *
	 * @param string $post_content Post content.
	 * @param array  $blocks       Blocks.
	 *
	 * @return string
	 */
	public function replace( $post_content, $blocks ) {
		preg_match_all( '/<!-- wp:yoast-seo\/table-of-contents.*-->.*<!-- \/wp:yoast-seo\/table-of-contents -->/iUs', $post_content, $matches );

		foreach ( $matches[0] as $index => $match ) {
			$post_content = \str_replace( $match, $blocks[ $index ], $post_content );
		}

		return $post_content;
	}

	/**
	 * Extract headings from the block content.
	 *
	 * @param string $content Block content.
	 *
	 * @return array
	 */
	public function get_headings( $content ) {
		preg_match_all( '|<a\s*href="([^"]+)"[^>]+>([^<]+)</a>|', $content, $matches );
		if ( empty( $matches ) || empty( $matches[0] ) ) {
			return [];
		}

		$headings = [];
		foreach ( $matches[0] as $link ) {
			$attrs      = HTML::extract_attributes( $link );
			$headings[] = [
				'key'     => uniqid( 'toc-' ),
				'link'    => $attrs['href'] ?? '',
				'content' => wp_strip_all_tags( $link ),
				'level'   => $attrs['data-level'] ?? '',
				'disable' => false,
			];
		}
		return $headings;
	}

	/**
	 * Get TOC title.
	 *
	 * @param string $html Block HTML.
	 *
	 * @return string
	 */
	public function get_toc_title( $html ) {
		preg_match( '#<h2.*?>(.*?)</h2>#i', $html, $found );
		return ! empty( $found[1] ) ? $found[1] : '';
	}

	/**
	 * Generate HTML.
	 *
	 * @param string $html Block html.
	 *
	 * @return string
	 */
	private function get_html( $html ) {
		$html = str_replace( 'wp-block-yoast-seo-table-of-contents yoast-table-of-contents', 'wp-block-rank-math-toc-block', $html );
		$html = str_replace( '</h2><ul>', '</h2><nav><ul>', $html );
		$html = str_replace( '</ul></div>', '</nav></ul></div>', $html );
		$html = preg_replace( '/data-level="([^"]*)"/', '', $html );

		return $html;
	}
}
