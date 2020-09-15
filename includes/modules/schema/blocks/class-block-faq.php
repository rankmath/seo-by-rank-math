<?php
/**
 * The FAQ Block
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Block_FAQ class.
 */
class Block_FAQ extends Block {

	/**
	 * The Constructor.
	 */
	public function __construct() {
		register_block_type(
			'rank-math/faq-block',
			[
				'render_callback' => [ $this, 'render' ],
				'editor_style'    => 'rank-math-block-admin',
				'attributes'      => [
					'listStyle'         => [
						'type'    => 'string',
						'default' => '',
					],
					'titleWrapper'      => [
						'type'    => 'string',
						'default' => 'h3',
					],
					'sizeSlug'          => [
						'type'    => 'string',
						'default' => 'thumbnail',
					],
					'questions'         => [
						'type'    => 'array',
						'default' => [],
						'items'   => [ 'type' => 'object' ],
					],
					'listCssClasses'    => [
						'type'    => 'string',
						'default' => '',
					],
					'titleCssClasses'   => [
						'type'    => 'string',
						'default' => '',
					],
					'contentCssClasses' => [
						'type'    => 'string',
						'default' => '',
					],
					'textAlign'         => [
						'type'    => 'string',
						'default' => 'left',
					],
				],
			]
		);

		add_filter( 'rank_math/schema/block/faq-block', [ $this, 'add_graph' ], 10, 2 );
	}

	/**
	 * FAQ rich snippet.
	 *
	 * @param array $data  Array of JSON-LD data.
	 * @param array $block JsonLD Instance.
	 *
	 * @return array
	 */
	public function add_graph( $data, $block ) {
		// Early bail.
		if ( ! $this->has_questions( $block['attrs'] ) ) {
			return $data;
		}

		if ( ! isset( $data['faqs'] ) ) {
			$data['faqs'] = [
				'@type'      => 'FAQPage',
				'mainEntity' => [],
			];
		}

		$permalink = get_permalink() . '#';
		foreach ( $block['attrs']['questions'] as $question ) {
			if ( empty( $question['title'] ) || empty( $question['content'] ) || empty( $question['visible'] ) ) {
				continue;
			}

			$data['faqs']['mainEntity'][] = [
				'@type'          => 'Question',
				'url'            => $permalink . $question['id'],
				'name'           => wp_strip_all_tags( $question['title'] ),
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text'  => $this->clean_text( $question['content'] ),
				],
			];
		}

		return $data;
	}

	/**
	 * Render block content
	 *
	 * @param array $attributes Array of atributes.
	 *
	 * @return string
	 */
	public function render( $attributes ) {
		// Early bail.
		if ( ! $this->has_questions( $attributes ) ) {
			return '';
		}

		$list_tag = $this->get_list_style( $attributes['listStyle'] );
		$item_tag = $this->get_list_item_style( $attributes['listStyle'] );

		// HTML.
		$out   = [];
		$out[] = sprintf( '<div id="rank-math-faq" class="rank-math-block"%s>', $this->get_styles( $attributes ) );
		$out[] = sprintf( '<%1$s class="rank-math-list %2$s">', $list_tag, $attributes['listCssClasses'] );

		// Questions.
		foreach ( $attributes['questions'] as $question ) {
			if ( empty( $question['title'] ) || empty( $question['content'] ) || empty( $question['visible'] ) ) {
				continue;
			}

			$out[] = sprintf( '<%1$s id="%2$s" class="rank-math-list-item">', $item_tag, $question['id'] );

			$out[] = sprintf(
				'<%1$s class="rank-math-question %2$s">%3$s</%1$s>',
				apply_filters( 'rank_math/blocks/faq/title_wrapper', $attributes['titleWrapper'] ),
				$attributes['titleCssClasses'],
				$question['title']
			);

			$out[] = sprintf(
				'<div class="rank-math-answer %1$s">%3$s%2$s</div>',
				$attributes['contentCssClasses'],
				wpautop( $question['content'] ),
				$this->get_image( $question, $attributes['sizeSlug'] )
			);

			$out[] = sprintf( '</%1$s>', $item_tag );
		}

		$out[] = sprintf( '</%1$s>', $list_tag );
		$out[] = '</div>';

		return join( "\n", $out );
	}

	/**
	 * Has questions.
	 *
	 * @param array $attributes Array of attributes.
	 *
	 * @return boolean
	 */
	private function has_questions( $attributes ) {
		return ! isset( $attributes['questions'] ) || empty( $attributes['questions'] ) ? false : true;
	}
}
