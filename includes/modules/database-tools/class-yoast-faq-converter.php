<?php
/**
 * The Yoast Block Converter.
 *
 * @since      1.0.37
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast_FAQ_Converter class.
 */
class Yoast_FAQ_Converter {

	/**
	 * Convert blocks to rank math
	 *
	 * @param array $block Block to convert.
	 *
	 * @return array
	 */
	public function convert( $block ) {
		$new_block = [
			'blockName' => 'rank-math/faq-block',
			'attrs'     => [
				'listStyle'         => '',
				'textAlign'         => 'left',
				'titleWrapper'      => 'h3',
				'listCssClasses'    => '',
				'titleCssClasses'   => '',
				'contentCssClasses' => '',
				'questions'         => array_map( [ $this, 'get_question' ], $block['attrs']['questions'] ),
				'className'         => isset( $block['attrs']['className'] ) ? $block['attrs']['className'] : '',
			],
		];

		$new_block['innerContent'][] = $this->get_html( $new_block['attrs'] );

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
		preg_match_all( '/<!-- wp:yoast\/faq-block.*-->.*<!-- \/wp:yoast\/faq-block -->/iUs', $post_content, $matches );

		foreach ( $matches[0] as $index => $match ) {
			$post_content = \str_replace( $match, $blocks[ $index ], $post_content );
		}

		return $post_content;
	}

	/**
	 * Gormat questions.
	 *
	 * @param array $question Question.
	 *
	 * @return array
	 */
	public function get_question( $question ) {
		return [
			'id'      => uniqid( 'faq-question-' ),
			'visible' => true,
			'title'   => $question['jsonQuestion'],
			'content' => $question['jsonAnswer'],
		];
	}

	/**
	 * [get_html description]
	 *
	 * @param array $attributes [description].
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
				'<div class="rank-math-answer">%2$s</div>',
				$attributes['titleWrapper'],
				$question['content']
			);

			$out[] = '</div>';
		}

		$out[] = '</div>';

		return join( '', $out );
	}
}
