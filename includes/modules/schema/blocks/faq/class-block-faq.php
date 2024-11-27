<?php
/**
 * The Faq Block
 *
 * @since      1.0.233
 * @package    RankMath
 * @subpackage RankMath\Faq
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use WP_Block_Type_Registry;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Faq Block class.
 */
class Block_FAQ extends Block {

	use Hooker;

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
			RANK_MATH_PATH . 'includes/modules/schema/blocks/faq/block.json',
			[
				'render_callback' => [ $this, 'render' ],
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
				'url'            => esc_url( $permalink . $question['id'] ),
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
		$out[] = sprintf( '<%1$s class="rank-math-list %2$s">', $list_tag, esc_attr( $attributes['listCssClasses'] ) );

		// Questions.
		foreach ( $attributes['questions'] as $question ) {
			if ( empty( $question['title'] ) || empty( $question['content'] ) || empty( $question['visible'] ) ) {
				continue;
			}

			if ( empty( $question['id'] ) ) {
				$question['id'] = 'rm-faq-' . md5( $question['title'] );
			}

			$out[] = sprintf( '<%1$s id="%2$s" class="rank-math-list-item">', $item_tag, esc_attr( $question['id'] ) );

			$out[] = sprintf(
				'<%1$s class="rank-math-question %2$s">%3$s</%1$s>',
				self::get()->get_title_wrapper( $attributes['titleWrapper'] ),
				esc_attr( $attributes['titleCssClasses'] ),
				wp_kses_post( $question['title'] )
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

		return apply_filters(
			'rank_math/schema/block/faq/content',
			wp_kses_post( join( "\n", $out ) ),
			$out,
			$attributes
		);
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
