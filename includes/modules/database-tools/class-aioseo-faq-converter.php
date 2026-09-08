<?php
/**
 * The AIOSEO FAQ Block Converter.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

defined( 'ABSPATH' ) || exit;

/**
 * AIOSEO_FAQ_Converter class.
 *
 * AIOSEO stores every FAQ item as its own standalone `aioseo/faq` block instead
 * of a single block holding all the questions, so this converter merges every
 * `aioseo/faq` block found in a post into a single Rank Math FAQ block.
 */
class AIOSEO_FAQ_Converter {

	/**
	 * Convert FAQ blocks to Rank Math.
	 *
	 * @param array $blocks AIOSEO FAQ blocks to convert.
	 *
	 * @return array
	 */
	public function convert( $blocks ) {
		if ( empty( $blocks ) ) {
			return [];
		}

		$new_block = [
			'blockName' => 'rank-math/faq-block',
			'attrs'     => [
				'listStyle'         => '',
				'textAlign'         => 'left',
				'titleWrapper'      => $this->get_title_wrapper( $blocks[0] ),
				'listCssClasses'    => '',
				'titleCssClasses'   => '',
				'contentCssClasses' => '',
				'questions'         => array_map( [ $this, 'get_question' ], $blocks ),
				'className'         => '',
			],
		];

		$new_block['innerContent'][] = $this->get_html( $new_block['attrs'] );

		return $new_block;
	}

	/**
	 * Replace block(s) in content.
	 *
	 * Every AIOSEO FAQ block found in the content is merged into a single Rank
	 * Math FAQ block. The first occurrence is replaced with the merged block
	 * markup and the remaining occurrences are removed.
	 *
	 * @param string $post_content Post content.
	 * @param array  $blocks       Converted block(s).
	 *
	 * @return string
	 */
	public function replace( $post_content, $blocks ) {
		preg_match_all( '/<!-- wp:aioseo\/faq.*-->.*<!-- \/wp:aioseo\/faq -->/iUs', $post_content, $matches );

		foreach ( $matches[0] as $index => $match ) {
			$post_content = \str_replace( $match, 0 === $index ? $blocks[0] : '', $post_content );
		}

		return $post_content;
	}

	/**
	 * Format question.
	 *
	 * @param array $block Parsed AIOSEO FAQ block.
	 *
	 * @return array
	 */
	public function get_question( $block ) {
		$attrs = ! empty( $block['attrs'] ) ? $block['attrs'] : [];

		return [
			'id'      => uniqid( 'faq-question-' ),
			'visible' => empty( $attrs['hidden'] ),
			'title'   => $attrs['question'] ?? '',
			'content' => $this->get_answer( $block ),
		];
	}

	/**
	 * Get the title wrapper tag used for the merged block.
	 *
	 * @param array $block Parsed AIOSEO FAQ block.
	 *
	 * @return string
	 */
	private function get_title_wrapper( $block ) {
		$attrs = ! empty( $block['attrs'] ) ? $block['attrs'] : [];

		return ! empty( $attrs['tagName'] ) ? $attrs['tagName'] : 'h3';
	}

	/**
	 * Get the answer HTML by rendering the FAQ block's inner blocks.
	 *
	 * The answer in AIOSEO is stored as nested blocks rather than a plain
	 * string, so we render them to get the final HTML.
	 *
	 * @param array $block Parsed AIOSEO FAQ block.
	 *
	 * @return string
	 */
	private function get_answer( $block ) {
		if ( empty( $block['innerBlocks'] ) ) {
			return '';
		}

		$parts = [];
		foreach ( $block['innerBlocks'] as $inner_block ) {
			$part = trim( \render_block( $inner_block ) );
			if ( '' !== $part ) {
				$parts[] = $part;
			}
		}

		return implode( "\n", $parts );
	}

	/**
	 * Generate HTML.
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	private function get_html( $attributes ) {
		// HTML.
		$out = [ '<div class="wp-block-rank-math-faq-block">' ];

		// Questions.
		foreach ( $attributes['questions'] as $question ) {
			if ( empty( $question['title'] ) || empty( $question['content'] ) || empty( $question['visible'] ) ) {
				continue;
			}

			$out[] = '<div class="rank-math-faq-item">';
			$out[] = sprintf(
				'<%1$s class="rank-math-question">%2$s</%1$s>',
				$attributes['titleWrapper'],
				$question['title']
			);

			$out[] = sprintf(
				'<div class="rank-math-answer">%1$s</div>',
				$question['content']
			);

			$out[] = '</div>';
		}

		$out[] = '</div>';

		return join( '', $out );
	}
}
