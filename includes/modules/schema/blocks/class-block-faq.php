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

use WP_Block_Type_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Block_FAQ class.
 */
class Block_FAQ extends Block {

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	private $block_type = 'rank-math/faq-block';

	/**
	 * The single instance of the class.
	 *
	 * @var Block_FAQ
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_FAQ instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Block_FAQ
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Block_FAQ ) ) {
			self::$instance = new Block_FAQ();
		}

		return self::$instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( WP_Block_Type_Registry::get_instance()->is_registered( $this->block_type ) ) {
			return;
		}

		register_block_type(
			$this->block_type,
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
	 * Add FAQ schema data in JSON-LD array.
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

			$question['title']   = do_shortcode( $question['title'] );
			$question['content'] = do_shortcode( $question['content'] );

			if ( empty( $question['id'] ) ) {
				$question['id'] = 'rm-faq-' . md5( $question['title'] );
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
	 * Render block content.
	 *
	 * @param array $attributes Array of atributes.
	 * @return string
	 */
	public static function markup( $attributes = [] ) {
		$list_tag = self::get()->get_list_style( $attributes['listStyle'] );
		$item_tag = self::get()->get_list_item_style( $attributes['listStyle'] );
		$class    = 'rank-math-block';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . esc_attr( $attributes['className'] );
		}

		// HTML.
		$out   = [];
		$out[] = sprintf( '<div id="rank-math-faq" class="%1$s"%2$s>', $class, self::get()->get_styles( $attributes ) );
		$out[] = sprintf( '<%1$s class="rank-math-list %2$s">', $list_tag, $attributes['listCssClasses'] );

		// Questions.
		foreach ( $attributes['questions'] as $question ) {
			if ( empty( $question['title'] ) || empty( $question['content'] ) || empty( $question['visible'] ) ) {
				continue;
			}

			if ( empty( $question['id'] ) ) {
				$question['id'] = 'rm-faq-' . md5( $question['title'] );
			}

			$out[] = sprintf( '<%1$s id="%2$s" class="rank-math-list-item">', $item_tag, $question['id'] );

			$out[] = sprintf(
				'<%1$s class="rank-math-question %2$s">%3$s</%1$s>',
				apply_filters( 'rank_math/blocks/faq/title_wrapper', $attributes['titleWrapper'] ),
				$attributes['titleCssClasses'],
				$question['title']
			);

			$out[] = '<div class="rank-math-answer ' . esc_attr( $attributes['contentCssClasses'] ) . '">';

			if ( ! empty( $question['imageUrl'] ) ) {
				$out[] = '<img src="' . esc_url( $question['imageUrl'] ) . '" />';
			} else {
				$out[] = self::get()->get_image( $question, $attributes['sizeSlug'] );
			}

			$out[] = self::get()->normalize_text( $question['content'], 'faq' );
			$out[] = '</div>';

			$out[] = sprintf( '</%1$s>', $item_tag );
		}

		$out[] = sprintf( '</%1$s>', $list_tag );
		$out[] = '</div>';

		return join( "\n", $out );
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

		return self::markup( $attributes );
	}

	/**
	 * Check if FAQ block has questions data.
	 *
	 * @param array $attributes Array of attributes.
	 *
	 * @return boolean
	 */
	private function has_questions( $attributes ) {
		return ! isset( $attributes['questions'] ) || empty( $attributes['questions'] ) ? false : true;
	}
}
