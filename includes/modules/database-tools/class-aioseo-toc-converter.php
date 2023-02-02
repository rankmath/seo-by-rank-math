<?php
/**
 * The AIOSEO TOC Block Converter.
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use MyThemeShop\Helpers\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * AIOSEO_TOC_Converter class.
 */
class AIOSEO_TOC_Converter {

	/**
	 * Convert TOC blocks to Rank Math.
	 *
	 * @param array $block Block to convert.
	 *
	 * @return array
	 */
	public function convert( $block ) {
		$attributes = $block['attrs'];
		$headings   = [];
		$this->get_headings( $attributes['headings'], $headings );
		$new_block  = [
			'blockName' => 'rank-math/toc-block',
			'attrs'     => [
				'title'           => '',
				'headings'        => $headings,
				'listStyle'       => $attributes['listStyle'] ?? 'ul',
				'titleWrapper'    => 'h2',
				'excludeHeadings' => [],
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
		preg_match_all( '/<!-- wp:aioseo\/table-of-contents.*-->.*<!-- \/wp:aioseo\/table-of-contents -->/iUs', $post_content, $matches );

		foreach ( $matches[0] as $index => $match ) {
			$post_content = \str_replace( $match, $blocks[ $index ], $post_content );
		}

		return $post_content;
	}

	/**
	 * Get headings from the content.
	 *
	 * @param array $data     Block data.
	 * @param array $headings Headings.
	 *
	 * @return array
	 */
	public function get_headings( $data, &$headings ) {
		foreach ( $data as $heading ) {
			$headings[] = [
				'key'     => $heading['blockClientId'],
				'link'    => '#' . $heading['anchor'],
				'content' => ! empty( $heading['editedContent'] ) ? $heading['editedContent'] : $heading['content'],
				'level'   => ! empty( $heading['editedLevel'] ) ? $heading['editedLevel'] : $heading['level'],
				'disable' => ! empty( $heading['hidden'] ),
			];

			if ( ! empty( $heading['headings'] ) ) {
				$this->get_headings( $heading['headings'], $headings );
			}
		}
	}

	/**
	 * Get TOC title.
	 *
	 * @param string $html Block HTML.
	 *
	 * @return string
	 */
	public function get_toc_title( $html ) {
		preg_match('#<h2.*?>(.*?)</h2>#i', $html, $found);
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
		$html = str_replace( 'wp-block-aioseo-table-of-contents', 'wp-block-rank-math-toc-block', $html );
		$html = str_replace( '<div class="wp-block-rank-math-toc-block"><ul>', '<div class="wp-block-rank-math-toc-block"><nav><ul>', $html );
		$html = str_replace( '</ul></div>', '</nav></ul></div>', $html );

		return $html;
	}
}
